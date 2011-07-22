/*

file:	 		get_histogram.h
last modified:		22/07/11
last modified by:	rmb	

*/	

#ifndef	GET_HISTOGRAM_H_
#define	GET_HISTOGRAM_H_

#include <stdbool.h>

#include "highgui.h"
#include "cv.h"

#include <gsl/gsl_histogram2d.h>

gsl_histogram2d * get_patch_histogram_through_integral (int, int, int, int, int, int, gsl_histogram2d * [], bool);
gsl_histogram2d * get_patch_histogram (int, int, int, int, IplImage*, gsl_histogram2d *, bool);

#endif
