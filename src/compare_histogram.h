/*

file:	 		compare_histogram.h
last modified:		22/07/11
last modified by:	rmb	

*/	

#ifndef	COMPARE_HISTOGRAM_H_
#define	COMPARE_HISTOGRAM_H_

#include <gsl/gsl_histogram2d.h>

double comp_intersect_histogram2d_gsl (gsl_histogram2d *, gsl_histogram2d *, int, int);
double comp_emdL1_histogram2d_gsl (gsl_histogram2d *, gsl_histogram2d *, int, int);

#endif
