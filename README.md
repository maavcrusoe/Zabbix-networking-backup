# Zabbix-networking-backup
External script for Zabbix made with Python to get a full backup for your network devices group by groupID from zabbix and using macro to retreive SSH credentials stored in zabbix 
This tool is for Zabbix like Oxidized from LibreNMS

![Alt text](https://github.com/maavcrusoe/Zabbix-networking-backup/blob/main/example.jpg)

# Installation

1. Need Python3 and PIP installed
2. run pip3 install netmiko and pip3 install ping3
3. download this script and paste in: /usr/lib/zabbix/externalscripts/
4. Install module in zabbix (Admin > General > Modules > Sync modules)

# Config
1. Set your variables in the script
2. Put your SSH credentials in zabbix > Administration > macro ({$SSH_USER}, {$SSH_PWD}) if you use multiple vendors ({$SSH_HUAWEI_USER}, {$SSH_vendor_PWD})
3. execute python3 backup.py 
4. make a cron 0 23 * * * python3 /usr/lib/zabbix/externalscripts/backup.py  > /dev/null 2>&1
