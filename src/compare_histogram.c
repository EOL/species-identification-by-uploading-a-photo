/*

file:	 		compare_histogram.c
last modified:		22/07/11
last modified by:	rmb	

*/	

#include <stdlib.h>
#include <stdio.h>
#include <math.h>

#include "highgui.h"
#include "cv.h"

#include "emdL1.h"

#include <gsl/gsl_histogram2d.h>
#include <gsl/gsl_statistics_double.h>

double comp_intersect_histogram2d_gsl (gsl_histogram2d * patch_histogram, gsl_histogram2d * texture_histogram, int bins_x, int bins_y) {	// HIGH VALUE IS A POSITIVE MATCH

	double these_histogram_sums [2] = {gsl_histogram2d_sum(patch_histogram), gsl_histogram2d_sum(texture_histogram)};
	double min_histogram_sum = gsl_stats_min(these_histogram_sums, 1, 2);

	double these_bin_values [2];
	double cum_min_bin_values = 0.0;

	int i, j;

	for (j=0; j<bins_y; j++)
	{
		for (i=0; i<bins_x; i++)
		{
			these_bin_values[0] = gsl_histogram2d_get(patch_histogram, i, j);
			these_bin_values[1] = gsl_histogram2d_get(texture_histogram, i, j);

			cum_min_bin_values += gsl_stats_min(these_bin_values, 1, 2); 
		}
	}

	return cum_min_bin_values/min_histogram_sum;

}

double comp_emdL1_histogram2d_gsl (gsl_histogram2d * patch_histogram, gsl_histogram2d * texture_histogram, int bins_x, int bins_y) {	// LOW VALUE IS A POSITIVE MATCH

	EmdL1	em;	// EMD_L1 class

	double d = em.EmdDist(patch_histogram->bin,texture_histogram->bin,bins_x,bins_y);

	return d;

}

