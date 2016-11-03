package main

import (
	"fmt"
	"io"
	"log"
	"net"
)

func main() {
	l, err := net.Listen("tcp", ":2000")
	if err != nil {
		log.Fatal(err)
	}
	defer l.Close()
	for {
		conn, err := l.Accept()
		if err != nil {
			log.Fatal(err)
		}
		go func(c net.Conn) {
			buf := make([]byte, 1024)
			c.Read(buf)
			fmt.Println(string(buf))
			io.Copy(c, c)
			c.Close()
		}(conn)
	}
}
