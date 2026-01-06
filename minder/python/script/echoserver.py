#
# Echo server program
import socket

HOST = ''                 # Symbolic name meaning all available interfaces
PORT = 9100               # Arbitrary non-privileged port
data = ""
s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
s.bind((HOST, PORT))
s.listen(1)
while 1:
	conn, addr = s.accept()
	print 'Connected by', addr
	while 1:
		data = conn.recv(65536)
		if not data: break
		# trim cr and lf
		if data[-1] == "\n": data = data[0: -1]
		if data[-1] == "\r": data = data[0: -1]
		conn.send(data)
		print data
		if data == "QUIT": break
		if data == "QUITALL": break
	conn.close()
	print 'Closed by', addr
	if data == "QUITALL": break

