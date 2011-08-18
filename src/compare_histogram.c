/*

compare_histogram.c - compares two histograms using a specified metric

Copyright (C) 2011 Rob Barnsley (rmb@astro.ljmu.ac.uk)

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

*/

#include <stdlib.h>
#include <stdio.h>
#include <math.h>

#include "highgui.h"
#include "cv.h"

#include "emdL1.h"
#include "dd_head.h"

#include <gsl/gsl_histogram2d.h>
#include <gsl/gsl_statistics_double.h>

#include "compare_histogram.h"

double comp_intersect_histogram2d_gsl (gsl_histogram2d * patch_histogram, gsl_histogram2d * texture_histogram, int bins_x, int bins_y) {	// high value is a positive match

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

double comp_emdL1_histogram2d_gsl (gsl_histogram2d * patch_histogram, gsl_histogram2d * texture_histogram, int bins_x, int bins_y) {	// low value is positive match

	EmdL1	em;	// EMD_L1 class

	double d = em.EmdDist(patch_histogram->bin,texture_histogram->bin,bins_x,bins_y);

	return d;

}

double comp_dd_histogram2d_gsl  (gsl_histogram2d * patch_histogram, gsl_histogram2d * texture_histogram, int bins_x, int bins_y) {	// low value is positive match

	dd_dist	dd;	// DD class

	double d = dd.dd2D(patch_histogram->bin,texture_histogram->bin,bins_x,bins_y);

	return d;

}

