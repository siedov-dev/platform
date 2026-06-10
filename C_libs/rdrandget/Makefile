CFLAGS = -std=gnu99 -march=core-avx-i -O3
rdrandget: rdrandget.o
	gcc -fPIC -o rdrandget.o -c rdrandget.c
	gcc -shared -o librdrandget.so rdrandget.o

test: test.o
	gcc -o test test.c -L. -lrdrandget

clean:
	rm -f rdrandget.o librdrandget.so test test.o