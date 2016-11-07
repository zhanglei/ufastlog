package main

import (
	"bytes"
	"encoding/json"
	"flag"
	"fmt"
	"io/ioutil"
	"log"
	"net"
	"net/http"
	"os"
	"strings"
)

var listenAddr string
var upstreamAddr string

func init() {
	flag.StringVar(&listenAddr, "l", ":1043", "Listen addr and port")
	flag.StringVar(&upstreamAddr, "r", "http://127.0.0.1:9200/", "Upstream addr and port")
}

func main() {
	flag.Parse()
	flag.Usage = func() {
		fmt.Fprintln(os.Stderr, "Usage of %s:", os.Args[0])
		flag.PrintDefaults()
	}
	if listenAddr == "http://127.0.0.1:9200" {
		fmt.Println(fmt.Sprintf("Seems you have not set listen and upsteam server, type %s -h to see usage!", os.Args[0]))
	}
	addr, err := net.ResolveUDPAddr("udp", listenAddr)
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
		handleClient(l)
	}
}

type ESLog struct {
	RequestId string
	Time      string
	Msg       string
}

func handleClient(l *net.UDPConn) {
	buff := make([]byte, 655360)
	for {
		n, rAddr, err := l.ReadFromUDP(buff)
		if err != nil {
			log.Fatal(err)
		} else {
			go prepareMsg(buff, n)
			l.WriteToUDP([]byte("ok\n"), rAddr)
		}
	}
}

func prepareMsg(buff []byte, n int) {
	//msg length
	fmt.Println(n)
	if n < 135 { //The minimal lenght of a log struct
		return
	}
	//0-31 logprefix
	logprefix := string(bytes.Trim(buff[0:31], "\000"))
	fmt.Println(logprefix)
	//32-95 requestid
	requestid := string(bytes.Trim(buff[32:95], "\000"))
	fmt.Println(requestid)
	//96-127 time
	timeLog := string(bytes.Trim(buff[96:127], "\000"))
	fmt.Println(timeLog)
	//128-132 msgtype
	msgtype := string(bytes.Trim(buff[128:132], "\000"))
	fmt.Println(msgtype)
	//133-end
	msg := string(bytes.Trim(buff[133:], "\000"))
	fmt.Println(msg)
	fmt.Println("ok")
	newMsg := &ESLog{
		RequestId: requestid,
		Time:      timeLog,
		Msg:       msg,
	}
	oMsg, err := json.Marshal(newMsg)
	fmt.Println(string(oMsg))
	if err != nil {
		fmt.Println("Error when encoding as Json")
		return
	} else {
		putUrl := fmt.Sprintf("%s/%s/", logprefix, msgtype)
		fmt.Println("putUrl:", putUrl)
		doPost(oMsg, putUrl)
	}
}
func doPost(oMsg []byte, putUrl string) {
	client := &http.Client{}
	esurl := fmt.Sprintf("%s%s", upstreamAddr, putUrl)
	fmt.Println(esurl)
	fmt.Println(string(oMsg))
	req, err := http.NewRequest("POST", esurl, strings.NewReader(string(oMsg)))
	req.ContentLength = int64(len(string(oMsg)))
	resp, err := client.Do(req)
	if err != nil {
		log.Fatal(err)
	} else {
		defer resp.Body.Close()
		contents, err := ioutil.ReadAll(resp.Body)
		if err != nil {
			log.Fatal(err)
		}
		fmt.Println("The calculated length is:", len(string(contents)), "for the url:", putUrl)
		fmt.Println("   ", resp.StatusCode)
		hdr := resp.Header
		for key, value := range hdr {
			fmt.Println("   ", key, ":", value)
		}
		fmt.Println(string(contents))
	}

}
