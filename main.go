package main

import (
	"fmt"
	"log"
	"net"
	"os"
)

func main() {
	addr, err := net.ResolveUDPAddr("udp", ":1043")
	if err != nil {
		fmt.Println("Can't resolv address: ", err)
		os.Exit(1)
	}
	l, err := net.ListenUDP("udp", addr)
	if err != nil {
		log.Fatal(err)
		os.Exit(1)
	}
	defer l.Close()
	for {
		go handleClient(l)
	}
}

func handleClient(l *net.UDPConn) {
	buff := make([]byte, 655360)
	for {
		n, rAddr, err := l.ReadFromUDP(buff)
		if err != nil {
			log.Fatal(err)
		} else {
			fmt.Println(string(buff[0:n]))
			fmt.Println("ok")
			l.WriteToUDP([]byte("ok\n"), rAddr)
		}
	}
}
