#### daemon:react-http-server as ip|domain:port

    php console daemon:react-http-server --socket-mode=1 --worker-number=0

        INFO: Http Server [YusamHub\AppExt\ReactHttpServer\ReactHttpServer] started at [2023-02-17 21:47:27]
        INFO: --socket-mode: 1
        INFO: --worker-number: 0
        INFO: LISTEN: 0.0.0.0:18080

#### daemon:react-http-server as unix-socket

    php console daemon:react-http-server --socket-mode=2 --worker-number=0

        INFO: Http Server [YusamHub\AppExt\ReactHttpServer\ReactHttpServer] started at [2023-02-17 21:53:27]
        INFO: --socket-mode: 2
        INFO: --worker-number: 0
        INFO: Checking dir: /tmp/react-http-server-socks
        INFO: Success dir: /tmp/react-http-server-socks
        INFO: LISTEN: unix:///tmp/react-http-server-socks/server.worker0.sock

#### curl ip|domain:port

    curl -vvv -X GET http://localhost:18080

#### curl unix-socket

    curl --unix-socket /tmp/react-http-server-socks/server.worker0.sock -vvv -X GET http://localhost.loc
    curl --unix-socket /tmp/react-http-server-socks/server.worker0.sock -vvv -X GET http://mini-app-example-react-8074.loc/api/debug
