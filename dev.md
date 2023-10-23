#### vbox
    
    cd /var/www/home-www/yusam/github/yusam-hub/project-0001/server-auth-application

###### tail

    tail -f app-2023-03-21.log

###### composer

    composer update

###### console

    php console

###### debug

    php console debug:test

###### daemon

    php console daemon:telegram
    php console daemon:redis-queue
    php console daemon:rabbit-mq-consumer
    php console daemon:react-http-server
    php console daemon:web-socket-server

###### console client

    php console client:rabbit-mq-publisher hello-message
    php console client:web-socket-internal
    php console client:web-socket-external hello-message

###### console openapi + swagger-ui

    php console open-api:generate-json
    php console swagger-ui install

###### console db

    php console db:migrate

###### console show

    php console show:env
    php console show:server

###### check

    php console redis:check    
    php console db:check    
    php console smarty:check
    php console php-mailer:check    

###### maintenance

    php console maintenance:set-user-max-allow-applications 1 999