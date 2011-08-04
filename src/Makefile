#############################
# compilers, archivers etc. #
#############################

CC		= /usr/bin/g++

####################
# $(INCLUDES) flag #
####################

INCLUDES	= -I/usr/include/opencv -I/usr/include/gsl

####################
# $(INCLUDES) flag #
####################

LIB		= -L/usr/lib

#################
# make routines #
#################

get_histogram: get_histogram.c
	$(CC) $(CPPFLAGS) $(INCLUDES) $(LIB) -c get_histogram.c -lcv -lcxcore -lhighgui -lgsl -lm

compare_histogram: compare_histogram.c
	$(CC) $(CPPFLAGS) $(INCLUDES) $(LIB) -c compare_histogram.c -lcv -lcxcore -lhighgui -lgsl -lm

emdL1: emdL1.cpp 
	$(CC) $(CPPFLAGS) -c emdL1.cpp

dd_dist: dd_dist.cpp
	$(CC) $(CPPFLAGS) -c dd_dist.cpp	

histogram_backproject: histogram_backproject.c emdL1 dd_dist get_histogram compare_histogram
	$(CC) $(CPPFLAGS) $(INCLUDES) $(LIB) emdL1.o dd_dist.o get_histogram.o compare_histogram.o histogram_backproject.c -lcv -lcxcore -lhighgui -lgsl -lm -o histogram_backproject
	mv histogram_backproject ../bin/
	rm -ir *.o

clean:
	rm -ir ../bin/histogram_backproject



