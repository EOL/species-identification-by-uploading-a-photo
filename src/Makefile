##################################
# Makefile 			 #
# Last Modified Date:   22/07/11 #
##################################

#############################
# compilers, archivers etc. #
#############################

CC		= /usr/bin/g++
AR		= /usr/bin/ar

##################
# compiler flags #
##################

#CFLAGS		= -std=c99
#CPPFLAGS	= -Wall

#########################
# csh terminal commands #
#########################

CAT		= /bin/cat
GREP		= /bin/grep
MV		= /bin/mv
RM		= /bin/rm -i

####################
# $(INCLUDES) flag #
####################

INCLUDES	= -I/usr/include/opencv

#################
# make routines #
#################

get_histogram: get_histogram.c
	$(CC) $(CPPFLAGS) $(INCLUDES) -c get_histogram.c -lcv -lcxcore -lhighgui -lgsl -lm

compare_histogram: compare_histogram.c
	$(CC) $(CPPFLAGS) $(INCLUDES) -c compare_histogram.c -lcv -lcxcore -lhighgui -lgsl -lm

emdL1: emdL1.cpp 
	$(CC) $(CPPFLAGS) -c emdL1.cpp

match_texture: match_texture.c emdL1 get_histogram compare_histogram
	$(CC) $(CPPFLAGS) $(INCLUDES) emdL1.o get_histogram.o compare_histogram.o match_texture.c -lcv -lcxcore -lhighgui -lgsl -lm -o match_texture
	mv match_texture ../bin/
	rm -r *.o



