#### dockers

    docker exec -it yusam-php81 sh

    docker exec -it yusam-php81 sh -c "php -m"

###### tail

    docker exec -it yusam-php81 sh -c "cd /var/www/data/yusam/github/yusam-hub/project-0001/server-auth-application/storage/logs && tail -f app-2023-03-21.log"

###### composer

    docker exec -it yusam-php81 sh -c "cd /var/www/data/yusam/github/yusam-hub/project-0001/server-auth-application && composer update"

###### console

    docker exec -it yusam-php81 sh -c "cd /var/www/data/yusam/github/yusam-hub/project-0001/server-auth-application && php console"

###### db migrate

    docker exec -it yusam-php81 sh -c "cd /var/www/data/yusam/github/yusam-hub/project-0001/server-auth-application && php console db:migrate"

###### debug

    docker exec -it yusam-php81 sh -c "cd /var/www/data/yusam/github/yusam-hub/project-0001/server-auth-application && php console debug:test"

###### daemon

    docker exec -it yusam-php81 sh -c "cd /var/www/data/yusam/github/yusam-hub/project-0001/server-auth-application && php console daemon:telegram"

###### console client

    docker exec -it yusam-php81 sh -c "cd /var/www/data/yusam/github/yusam-hub/project-0001/server-auth-application && php console client:rabbit-mq-publisher hello-message"

    docker exec -it yusam-php81 sh -c "cd /var/www/data/yusam/github/yusam-hub/project-0001/server-auth-application && php console client:web-socket-internal"
    docker exec -it yusam-php81 sh -c "cd /var/www/data/yusam/github/yusam-hub/project-0001/server-auth-application && php console client:web-socket-external hello-message"

###### console openapi + swagger-ui

    docker exec -it yusam-php81 sh -c "cd /var/www/data/yusam/github/yusam-hub/project-0001/server-auth-application && php console open-api:generate-json"
    docker exec -it yusam-php81 sh -c "cd /var/www/data/yusam/github/yusam-hub/project-0001/server-auth-application && php console swagger-ui install"

###### console db

    docker exec -it yusam-php81 sh -c "cd /var/www/data/yusam/github/yusam-hub/project-0001/server-auth-application && php console db:check"
    docker exec -it yusam-php81 sh -c "cd /var/www/data/yusam/github/yusam-hub/project-0001/server-auth-application && php console db:migrate"

###### console show

    docker exec -it yusam-php81 sh -c "cd /var/www/data/yusam/github/yusam-hub/project-0001/server-auth-application && php console show:env"
    docker exec -it yusam-php81 sh -c "cd /var/www/data/yusam/github/yusam-hub/project-0001/server-auth-application && php console show:server"

###### console smarty

    docker exec -it yusam-php81 sh -c "cd /var/www/data/yusam/github/yusam-hub/project-0001/server-auth-application && php console smarty:check"

###### testing

    docker exec -it yusam-php81 sh
    cd '/var/www/data/yusam/github/yusam-hub/project-0001/server-auth-application/#dev'
    curl --unix-socket /tmp/react-http-server-socks/server.worker0.sock -vvv -X GET http://project-0001/server-auth-application-react-8074.loc/api/debug