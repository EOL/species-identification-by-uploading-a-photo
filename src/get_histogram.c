/*

file:	 		get_histogram.c
last modified:		22/07/11
last modified by:	rmb	

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

gsl_histogram2d * get_patch_histogram_through_integral (int x1, int x2, int y1, int y2, int img_input_w, int img_input_h, gsl_histogram2d * cum_histograms [], bool NORMALISE) {

	int x1_trans = x1 + 1;	// +1 to account for zero padding
	int x2_trans = x2 + 1;
	int y1_trans = y1 + 1;
	int y2_trans = y2 + 1;

	gsl_histogram2d * result = gsl_histogram2d_clone(cum_histograms[((y2_trans)*(img_input_w+1)) + x2_trans]);	// [x2_trans][y2_trans]
	gsl_histogram2d_add(result, cum_histograms[((y1_trans-1)*(img_input_w+1)) + x1_trans-1]);			// [x1_trans-1][y1_trans-1]
	gsl_histogram2d_sub(result, cum_histograms[((y2_trans)*(img_input_w+1)) + x1_trans-1]);				// [x1_trans-1][y2_trans]
	gsl_histogram2d_sub(result, cum_histograms[((y1_trans-1)*(img_input_w+1)) + x2_trans]);				// [x2_trans][y1_trans-1]

	if (NORMALISE == true)
		gsl_histogram2d_scale(result, 1/gsl_histogram2d_sum(result));

	//gsl_histogram2d_fprintf (stdout, result, "%g", "%g");	// DEBUG
	//printf("%f\n", gsl_histogram2d_sum(result));		// DEBUG

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

	if (NORMALISE == true)
		gsl_histogram2d_scale(histogram, 1/gsl_histogram2d_sum(histogram));

}
