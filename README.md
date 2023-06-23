# Zabbix-networking-backup
External script for Zabbix made with Python to get a full backup for your network devices group by groupID from zabbix and using macro to retreive SSH credentials stored in zabbix 
This tool is for Zabbix like Oxidized from LibreNMS

![Alt text](https://github.com/maavcrusoe/Zabbix-networking-backup/blob/main/example.png)

# Requirements
1. Python3
2. pip
3. netmiko
4. ping3
5. mysql.connector
6. coloredlogs
7. **Zabbix >=6**

```bash
  pip install -r requirements.txt
``` 

# Folder Structure

This PHP inside your Zabbix UI is used to get content file
```
  nano /usr/share/zabbix/get_files.php
```

## Zabbix Module
```
  cd /usr/share/zabbix/modules/backup-networking/
  views/my.address.php
  actions/MyAddress.php
```

## External Scripts
Directorio cron (external script)
```
cd /usr/lib/zabbix/externalscripts/
nano backup.py
```

# Installation

1. Need Python3 and PIP installed
2. download python script and paste in: /usr/lib/zabbix/externalscripts/
3. make cron with your interval time (every 24hours)
   ```
     0 23 * * * python3 /usr/lib/zabbix/externalscripts/backup.py >/dev/null 2>&1
   ```
5. Install module in zabbix (**Admin > General > Modules > Sync modules**)
  a. Put module folder inside /usr/share/zabbix/modules

# Config
1. Set your variables in the script
2. Put your SSH credentials in zabbix > Administration > macro ({$SSH_USER}, {$SSH_PWD}) if you use multiple vendors ({$SSH_HUAWEI_USER}, {$SSH_vendor_PWD})
   **Remember your macro pattern is your "key" in config (backup.py) **  
3. execute python3 backup.py 
4. make a cron 0 23 * * * python3 /usr/lib/zabbix/externalscripts/backup.py  > /dev/null 2>&1
