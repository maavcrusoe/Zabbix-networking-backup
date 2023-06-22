# All pre-installed besides Netmiko.
from csv import reader
from datetime import date, datetime
from netmiko import ConnectHandler
from ping3 import ping, verbose_ping
import getpass
import os
import sys
import mysql.connector
from mysql.connector import Error
import coloredlogs,logging
sys.tracebacklimit = 0

# create logger
logger = logging.getLogger(__name__)

# create console handler and set level to debug
ch = logging.StreamHandler()
ch.setLevel(logging.DEBUG)

# create formatter
formatter = logging.Formatter('%(asctime)s - %(name)s - %(levelname)s | %(message)s')

# add formatter to ch
ch.setFormatter(formatter)

# add ch to logger
logger.addHandler(ch)

# Create a logger object.
coloredlogs.DEFAULT_LOG_FORMAT = '%(asctime)s -  %(levelname)s | %(message)s'
coloredlogs.DEFAULT_DATE_FORMAT = '%m/%d/%Y %H:%M:%S'
coloredlogs.install(level='DEBUG', logger=logger)

###### VARS/obj ######
groupid = 25                                # set your groupID
username = ""                               # ssh username 
password = ""                               # ssh password
key = "SSH"                                 # ssh {MACRO.ID}
dir_backup = "/home/user/backupdir/"        # set backup directory
now = datetime.now()                        # Current time and formats it to the North American time of Month, Day, and Year.
dt_string = now.strftime("%Y-%m-%d_%H-%M")
logging.basicConfig(filename=dir_backup+'executions.log', format='%(asctime)s -  %(levelname)s | %(message)s', datefmt='%m/%d/%Y %H:%M:%S %p')
####################

# Checks if the folder exists, if not, it creates it.
if not os.path.exists(dir_backup):
    os.makedirs(dir_backup)

hosts = []
users = []     

# MySQL config
def connectionMySQL():
    connection = mysql.connector.connect(host='SERVER',
                                         database='DATABASE',
                                         user='USER',
                                         password='PASSWORD')
    return connection

# get all hosts by network group
def get_AllHosts(groupid):
    connection = connectionMySQL()
    if connection != 0:
        try:
            sql = ('SELECT hosts_groups.groupid, hosts_groups.hostid, hosts.host, hosts.status '
                    'FROM hosts_groups JOIN hosts ON hosts.hostid = hosts_groups.hostid '
                    'WHERE hosts_groups.groupid = '+str(groupid)+' ')
            cursor = connection.cursor()
            cursor.execute(sql)
            records = cursor.fetchall()
            #print(records)
            if records == None:
                pass
            else:
                return records
        except Error as e:
            print("Error reading data from MySQL table", e)
            logger.error(e)
        finally:
            if connection.is_connected():
                cursor.close()
                connection.close() 
        return True

# put your {MACRO.NAME} from zabbix 
def get_sshData(key):

    user = None
    password = None
    connection = connectionMySQL()
    if connection != 0:
        try:
            sql = 'SELECT macro, value, description FROM globalmacro WHERE macro LIKE "%{}%"'.format(key)
            cursor = connection.cursor()
            cursor.execute(sql)
            records = cursor.fetchall()
            if records is not None:
                for x in records:
                    ssh = {}
                    if key in x[0]:
                        description = x[2]
                        if "USER" in x[0]:
                            #print("user found")
                            user = x[1]
                        if "PWD" in x[0]:
                            #print("pwd found")
                            password = x[1]
                    
                    if user is not None and password is not None:
                        ssh = {
                            "user": user,
                            "password": password,
                            "description": description
                        }
                        users.append(ssh)
                        user = None
                        password = None

                return users
        except Error as e:
            print("Error reading data from MySQL table:", e)
            logger.error(e)
        finally:
            if connection.is_connected():
                cursor.close()
                connection.close()
    return None



# Gives us the information we need to connect.
def get_saved_config(host, username, password,device_type):
    device = {
        "device_type": device_type,
        'ip': host,
        'username': username,
        'password': password,
    }

    # Creates the connection to the device.
    print("Connecting to SSH host", host)
    logger.debug("Connectiong to SSH "+str(host)+" ...")
    if device_type == "huawei":
        try:
            net_connect = ConnectHandler (** device)
            net_connect.enable()
            # Gets the running configuration.
            print("Executing command..")
            output = net_connect.send_command("dis current-configuration")
            print("Saving confing to file")
            backupFile = open(dir_backup + host + ".txt", "w+")
            backupFile.write(output)
            print("Outputted to "+ dir_backup + host + ".txt!")
            return True
        except Error as e:
            logger.error(e)
            return False
    elif device.device_type == "cisco":
        try:
            # Creates the connection to the device.
            net_connect = ConnectHandler(** device)
            net_connect.enable()
            # Gets the running configuration.
            output = net_connect.send_command("show run")
            # Gets and splits the hostname for the output file name.
            hostname = net_connect.send_command("show ver | i uptime")
            hostname = hostname.split()
            hostname = hostname[0]
            # Creates the text file in the backup-config folder with the special name, and writes to it.
            backupFile = open("backup-config/" + host + ".txt", "w+")
        except Error as e:
            logger.error(e)
            return False
  

# start script
def main():
    i = 0
    result = get_sshData(key)
    #print("result:",result)             # retreive all ssh logins
    #print("user 1:",result[0]["user"])  # retreive 1st user 
    #print("user 2:",result[1]["user"])  # 2nd user if you want to retreive password use result[x]["password"]

    # save results into var
    username = result[0]["user"]
    password = result[0]["password"]
    device_type = result[0]["description"].decode() # we need to decode because zabbix return Bytes..

    allHosts = get_AllHosts(groupid)
    print("Found",len(allHosts), " hosts")

    # loop and save to obj values
    for row in allHosts:
        #print(i)
        obj = {
            "groupid": row[0],
            "hostid": row[1],
            "hostname" : row[2],
            "status" : row[3]
        }
        hosts.append(obj)
        i += 1

    for x in hosts:
        print("Getting config from: ",x["hostname"], " >> ",x["hostid"])
        print("width this ssh credentials:", str(device_type))
        print(username)
        #print(password)
        result = get_saved_config(x["hostname"],username,password,str(device_type))
        if result == True:
            logger.debug(str(x["hostname"])+" config saved succefully")
            return True
        else:
            logger.error(str(x["hostname"])+" Problem getting config...")
            return False


# execute script
main()
