package main

import (
	"bufio"
	"fmt"
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
			b := bufio.NewReader(c)
			for {
				line, err := b.ReadString('\n')
				if err != nil {
					break
				}
				fmt.Println(line)
				msg := []byte(line)
				c.Write(msg)
			}
		}(conn)
	}
}
