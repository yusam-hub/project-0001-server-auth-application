####Supervisor

    yum install supervisor
    systemctl start supervisord
    systemctl status supervisord
    systemctl enable supervisord
    systemctl stop supervisord

    cd /etc/supervisor.d/*.ini
    supervisorctl reread
    supervisorctl update

    supervisorctl start name:*
    supervisorctl stop name:*

    netstat -ntlp | grep LISTEN

