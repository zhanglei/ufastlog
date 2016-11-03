package main

import (
	"bufio"
	"io"
	"log"
	"net"
	"os"
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
				line, err := b.ReadBytes('\n')
				if err != nil {
					break
				}
				io.WriteString(os.Stdout, string(line))
				io.Copy(c, c)
			}
		}(conn)
	}
}
