/*

file:	 		match_texture.c
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

#include "compare_histogram.h"
#include "get_histogram.h"

int main(int argc, char **argv) {

	// method

	char *method	= strdup(argv[argc-1]);

	// set up input image colour spaces

	IplImage* img_input = cvLoadImage(argv[1], CV_LOAD_IMAGE_COLOR);		// read input image 
	IplImage* img_input_conv = cvCreateImage(cvGetSize(img_input), 8, 3);		// create converted input image 
	cvCvtColor(img_input, img_input_conv, CV_BGR2HSV);				// move to different colour space

	CvSize img_input_size = cvGetSize(img_input_conv);
	int img_input_w = img_input_size.width;
	int img_input_h = img_input_size.height;

	int img_input_num_pix = img_input_w * img_input_h;

	// set up histogram params

	int bins_x = 10, bins_y = 10; 
	double x_min = -1, x_max = 256, y_min = -1, y_max = 256;	// range is actually 0-255, but histogram is exclusive

	// set up cumulative histograms
 
	int i, j;

	gsl_histogram2d * cum_histograms [(img_input_w+1)*(img_input_h+1)];	// to make it computationally efficient, +1 is required to add zero padding	

	for (j=0; j<img_input_h+1; j++)
	{
		for (i=0; i<img_input_w+1; i++)
		{
			cum_histograms[((j)*(img_input_w+1)) + i] = gsl_histogram2d_alloc (bins_x, bins_y);				// [i][j] if 2D
			gsl_histogram2d_set_ranges_uniform (cum_histograms[((j)*(img_input_w+1)) + i], x_min, x_max, y_min, y_max); 	// [i][j] if 2D
		}
	}

	// propagate wavelet and add cumulative histograms to array

	CvScalar this_pix;
	int this_pix_index = 0;

	for (j=0; j<img_input_h; j++) 	// WAVELET PROPAGATION (STARTS FROM BOTTOM LEFT)
	{
		for (i=0; i<img_input_w; i++) 
		{
			this_pix = cvGet2D(img_input_conv, j, i);

			gsl_histogram2d_add(cum_histograms[((j+1)*(img_input_w+1)) + i+1], cum_histograms[((j+1)*(img_input_w+1)) + i]);	// add left adjacent cumulative histogram	[i+1][j+1]	[i][j+1]
			gsl_histogram2d_add(cum_histograms[((j+1)*(img_input_w+1)) + i+1], cum_histograms[((j)*(img_input_w+1)) + i+1]);	// add below adjacent cumulative histogram	[i+1][j+1]	[i+1][j]
			gsl_histogram2d_sub(cum_histograms[((j+1)*(img_input_w+1)) + i+1], cum_histograms[((j)*(img_input_w+1)) + i]);	// subtract left, below histogram to account for double adding	[i+1][j+1]	[i][j]

			gsl_histogram2d_increment(cum_histograms[((j+1)*(img_input_w+1)) + i+1], this_pix.val[0], this_pix.val[1]);	// 0 is H plane, 1 is S plane	[i+1][j+1]
		}
	}

	// cycle textures

	// set up clock
	clock_t start, end;
	start = clock();

	int this_texture_num, texture_start_num = 2, texture_end_num = argc-2;

	for (this_texture_num = texture_start_num; this_texture_num <= texture_end_num; this_texture_num++)
	{

		gsl_histogram2d * texture_histogram = gsl_histogram2d_alloc (bins_x, bins_y);
		gsl_histogram2d_set_ranges_uniform (texture_histogram, x_min, x_max, y_min, y_max); 

		IplImage* img_texture = cvLoadImage(argv[this_texture_num], CV_LOAD_IMAGE_COLOR);	// read texture image 
		IplImage* img_texture_conv = cvCreateImage(cvGetSize(img_texture), 8, 3);		// create converted image 
		cvCvtColor(img_texture, img_texture_conv, CV_BGR2HSV);	

		CvSize img_texture_size = cvGetSize(img_texture_conv);
		int img_texture_w = img_texture_size.width;
		int img_texture_h = img_texture_size.height;
		int img_texture_num_pix = img_texture_w * img_texture_h;

		// input/texture image size check
		
		if ((img_texture_w > img_input_w) || (img_texture_h > img_input_h))
		{
			//printf("Skipping texture due to dimension constraints.\n");
			printf("0.00");
			continue;
		}

		// populate texture histogram

		get_patch_histogram (0, img_texture_w, 0, img_texture_h, img_texture_conv, texture_histogram, true);

		// histogram backprojection

		gsl_histogram2d * this_patch_histogram = gsl_histogram2d_alloc (bins_x, bins_y);	
		gsl_histogram2d_set_ranges_uniform (this_patch_histogram, x_min, x_max, y_min, y_max); 

		int stride = 1;

		int num_iterations = ceil((img_input_w-img_texture_w+1)/stride)*ceil((img_input_h-img_texture_h+1)/stride);

		double d [num_iterations];		// array to hold comparison values

		int this_iteration = 0;

		for (j=0; j<ceil((img_input_h-img_texture_h+1)/stride); j++)
		{
			for (i=0; i<ceil((img_input_w-img_texture_w+1)/stride); i++)
			{
				this_patch_histogram = get_patch_histogram_through_integral(i, i+img_texture_w-1, j, j+img_texture_h-1, img_input_w, img_input_h, cum_histograms, true);
				//get_patch_histogram (i, i+img_texture_w, j, j+img_texture_h, img_input_conv, this_patch_histogram, true);

				// perform comparison
				if (strcmp(method, "INTERSECT") == 0) 
					d[this_iteration] = comp_intersect_histogram2d_gsl(this_patch_histogram, texture_histogram, bins_x, bins_y);
				else if (strcmp(method, "EMDL1") == 0) 
					d[this_iteration] = comp_emdL1_histogram2d_gsl(this_patch_histogram, texture_histogram, bins_x, bins_y);
				else {
					printf("Undefined method.\n");
					return 1;
				}

				// reset

				gsl_histogram2d_reset(this_patch_histogram);	
				this_iteration++;

			}
		}

		// free histograms

		gsl_histogram2d_free(this_patch_histogram);
		gsl_histogram2d_free(texture_histogram);

		//double best_result = gsl_stats_max(d, 1, num_iterations);	// INTERSECT
		double best_result = gsl_stats_min(d, 1, num_iterations);	// EMD-L1

		if (this_texture_num == texture_end_num)
			printf("%.2f", best_result);
		else 
			printf("%.2f,", best_result);

		// results

		//printf("Result:\t\t%f\n", best_result);
		//printf("Iterations:\t%d\n", num_iterations);

	}

	// free histograms

	for (j=0; j<img_input_h+1; j++)
	{
		for (i=0; i<img_input_w+1; i++)
		{
			gsl_histogram2d_free(cum_histograms[((j)*(img_input_w+1)) + i]);		// [i][j]
		}
	}

	// stop the clock

	end = clock();
	double elapsed = ((double) (end - start)) / CLOCKS_PER_SEC;
	//printf("Time elapsed:\t%f\n", elapsed);

}



