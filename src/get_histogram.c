/*

get_histogram.c - returns the histogram of a patch

Copyright (C) 2011 Rob Barnsley (rmb@astro.ljmu.ac.uk)

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

*/	

#include <string.h>
#include <stdlib.h>
#include <stdio.h>
#include <time.h>
#include <math.h>
#include <stdbool.h>

#include "highgui.h"
#include "cv.h"

#include <gsl/gsl_histogram2d.h>
#include <gsl/gsl_statistics_double.h>

#include "get_histogram.h"

gsl_histogram2d * get_patch_histogram_through_integral (int x1, int x2, int y1, int y2, int img_input_w, int img_input_h, gsl_histogram2d * cum_histograms [], bool NORMALISE) {

	int x1_trans = x1 + 1;	// +1 to account for zero padding
	int x2_trans = x2 + 1;
	int y1_trans = y1 + 1;
	int y2_trans = y2 + 1;

	gsl_histogram2d * result = gsl_histogram2d_clone(cum_histograms[((y2_trans)*(img_input_w+1)) + x2_trans]);	// [x2_trans][y2_trans]
	gsl_histogram2d_add(result, cum_histograms[((y1_trans-1)*(img_input_w+1)) + x1_trans-1]);			// [x1_trans-1][y1_trans-1]
	gsl_histogram2d_sub(result, cum_histograms[((y2_trans)*(img_input_w+1)) + x1_trans-1]);				// [x1_trans-1][y2_trans]
	gsl_histogram2d_sub(result, cum_histograms[((y1_trans-1)*(img_input_w+1)) + x2_trans]);				// [x2_trans][y1_trans-1]

	if (NORMALISE == true)	// normalisation scales the resulting histogram to have a total count of 1
		gsl_histogram2d_scale(result, 1/gsl_histogram2d_sum(result));

	return result;

}

gsl_histogram2d * get_patch_histogram (int x1, int x2, int y1, int y2, IplImage* image, gsl_histogram2d * histogram, bool NORMALISE) {

	CvScalar this_pix;
	int i, j, this_pix_index = 0;

	for (j=y1; j<y2; j++) 
	{
		for (i=x1; i<x2; i++) 
		{
			this_pix = cvGet2D(image, j, i);
			gsl_histogram2d_increment(histogram, this_pix.val[0], this_pix.val[1]);	// 0 is H plane, 1 is S plane

			this_pix_index++;
		}
	}

	if (NORMALISE == true)	// normalisation scales the resulting histogram to have a total count of 1
		gsl_histogram2d_scale(histogram, 1/gsl_histogram2d_sum(histogram));

}
