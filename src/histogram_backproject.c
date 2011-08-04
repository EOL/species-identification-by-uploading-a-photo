/*

histogram_backproject.c - a histogram backprojection algorithm

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

----------------------------------------------------------------------

NOTES:

1. The binary call takes the form of:

   > match_texture 

	[/path/to/input_img] 
	[/path/to/texture_1 /path/to/texture_2 ... /path/to/texture_n] 
	[METHOD]

2. Histogram matrices are assumed to be arranged such that the 
   [i,j]-th element is located in the position (j*(n1) + i).

3. The integral histogram code is an adoption of the algorithm taken
   from:

	2005. Integral Histogram: A Fast Way To Extract Histograms 
	in Cartesian Spaces. In Proceedings of the 2005 IEEE Computer 
	Society Conference on Computer Vision and Pattern Recognition 
	(CVPR'05) - Volume 1 - Volume 01 (CVPR '05), Vol. 1. IEEE 
	Computer Society, Washington, DC, USA, 829-836. 
	DOI=10.1109/CVPR.2005.188 
	http://dx.doi.org/10.1109/CVPR.2005.188 

4. The supported comparison methods, specified by [METHOD], are:

	- Histogram Intersect (method="INTERSECT")
	- EMD-L1 (method="EMDL1")

	Haibin Ling and Kazunori Okada. 2007. An Efficient Earth Mover's 
	Distance Algorithm for Robust Histogram Comparison. IEEE Trans. 
	Pattern Anal. Mach. Intell. 29, 5 (May 2007), 840-853. 
	DOI=10.1109/TPAMI.2007.1058 
	http://dx.doi.org/10.1109/TPAMI.2007.1058 

	- Diffusion Distance (method="DD")

	Haibin Ling and Kazunori Okada. 2006. Diffusion Distance for 
	Histogram Comparison. In Proceedings of the 2006 IEEE Computer 
	Society Conference on Computer Vision and Pattern Recognition - 
	Volume 1  (CVPR '06), Vol. 1. IEEE Computer Society, Washington, 
	DC, USA, 246-253. 
	DOI=10.1109/CVPR.2006.99 
	http://dx.doi.org/10.1109/CVPR.2006.99 

5. Returned is a CSV list of the metric output for each texture.

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

	// REALLOCATION OF INPUT VARS
	// ****************************************************

	char *method	= strdup(argv[argc-1]);
	
	// PROCESS INPUT IMG
	// ****************************************************
	// convert from RGB to HSV colour space

	IplImage* img_input = cvLoadImage(argv[1], CV_LOAD_IMAGE_COLOR);		// read input image 
	IplImage* img_input_conv = cvCreateImage(cvGetSize(img_input), 8, 3);		// create converted input image 
	cvCvtColor(img_input, img_input_conv, CV_BGR2HSV);				// move to different colour space

	// ****************************************************
	// set up histogram parameters

	CvSize img_input_size = cvGetSize(img_input_conv);

	int img_input_w = img_input_size.width;
	int img_input_h = img_input_size.height;

	int img_input_num_pix = img_input_w * img_input_h;

	int bins_x = 10, bins_y = 10; 
	double x_min = -1, x_max = 256, y_min = -1, y_max = 256;	// range is actually 0-255, but histogram is exclusive

	// ****************************************************
	// set up integral histogram
 
	int i, j;

	gsl_histogram2d * cum_histograms [(img_input_w+1)*(img_input_h+1)];	// to make it computationally efficient, +1 is required to add zero padding	

	for (j=0; j<img_input_h+1; j++)
	{
		for (i=0; i<img_input_w+1; i++)
		{
			cum_histograms[((j)*(img_input_w+1)) + i] = gsl_histogram2d_alloc (bins_x, bins_y);			
			gsl_histogram2d_set_ranges_uniform (cum_histograms[((j)*(img_input_w+1)) + i], x_min, x_max, y_min, y_max);
		}
	}

	// ****************************************************
	// populate integral histogram

	CvScalar this_pix;
	int this_pix_index = 0;

	for (j=0; j<img_input_h; j++) 	// propagation starts from (0,0)
	{
		for (i=0; i<img_input_w; i++) 
		{
			this_pix = cvGet2D(img_input_conv, j, i);

			gsl_histogram2d_add(cum_histograms[((j+1)*(img_input_w+1)) + i+1], cum_histograms[((j+1)*(img_input_w+1)) + i]);	// add left adjacent cumulative histogram [i+1][j+1],[i][j+1]
			gsl_histogram2d_add(cum_histograms[((j+1)*(img_input_w+1)) + i+1], cum_histograms[((j)*(img_input_w+1)) + i+1]);	// add below adjacent cumulative histogram [i+1][j+1],[i+1][j]
			gsl_histogram2d_sub(cum_histograms[((j+1)*(img_input_w+1)) + i+1], cum_histograms[((j)*(img_input_w+1)) + i]);		// subtract left, below histogram to account for double adding [i+1][j+1],[i][j]

			gsl_histogram2d_increment(cum_histograms[((j+1)*(img_input_w+1)) + i+1], this_pix.val[0], this_pix.val[1]);	// 0 is H plane, 1 is S plane	[i+1][j+1]
		}
	}

	// PROCESS TEXTURES
	// ****************************************************

	// clock_t start, end;
	// start = clock();

	int this_texture_num, texture_start_num = 2, texture_end_num = argc-2;

	for (this_texture_num = texture_start_num; this_texture_num <= texture_end_num; this_texture_num++)
	{

		// ****************************************************
		// convert from RGB to HSV colour space

		gsl_histogram2d * texture_histogram = gsl_histogram2d_alloc (bins_x, bins_y);
		gsl_histogram2d_set_ranges_uniform (texture_histogram, x_min, x_max, y_min, y_max); 

		IplImage* img_texture = cvLoadImage(argv[this_texture_num], CV_LOAD_IMAGE_COLOR);	// read texture image 
		IplImage* img_texture_conv = cvCreateImage(cvGetSize(img_texture), 8, 3);		// create converted image 
		cvCvtColor(img_texture, img_texture_conv, CV_BGR2HSV);					// move to different colour space

		// ****************************************************
		// set up histogram parameters

		CvSize img_texture_size = cvGetSize(img_texture_conv);
		int img_texture_w = img_texture_size.width;
		int img_texture_h = img_texture_size.height;
		int img_texture_num_pix = img_texture_w * img_texture_h;

		// ****************************************************
		// input/texture image size check (backprojection will fail if the texture has dimensions larger than the input img)
		
		if ((img_texture_w > img_input_w) || (img_texture_h > img_input_h))	// skip this texture
		{
			printf(",");
			continue;
		}

		// populate texture histogram

		get_patch_histogram (0, img_texture_w, 0, img_texture_h, img_texture_conv, texture_histogram, true);

		// set up backprojection parameters

		gsl_histogram2d * this_patch_histogram = gsl_histogram2d_alloc (bins_x, bins_y);	
		gsl_histogram2d_set_ranges_uniform (this_patch_histogram, x_min, x_max, y_min, y_max); 

		int stride = 1;		// number of pixels to skip during iterations (1 = no skip)

		int num_iterations = ceil((img_input_w-img_texture_w+1)/stride)*ceil((img_input_h-img_texture_h+1)/stride);

		double d [num_iterations];	// array to hold comparison values

		int this_iteration = 0;

		// perform backprojection

		for (j=0; j<ceil((img_input_h-img_texture_h+1)/stride); j++)
		{
			for (i=0; i<ceil((img_input_w-img_texture_w+1)/stride); i++)
			{
				this_patch_histogram = get_patch_histogram_through_integral(i, i+img_texture_w-1, j, j+img_texture_h-1, img_input_w, img_input_h, cum_histograms, true);	// get equivalent histogram for this patch from integral histogram of input img

				//get_patch_histogram (i, i+img_texture_w, j, j+img_texture_h, img_input_conv, this_patch_histogram, true);	// get equivalent histogram for this patch from histogram of input img

				if (strcmp(method, "INTERSECT") == 0) 
					d[this_iteration] = comp_intersect_histogram2d_gsl(this_patch_histogram, texture_histogram, bins_x, bins_y);
				else if (strcmp(method, "EMDL1") == 0) 
					d[this_iteration] = comp_emdL1_histogram2d_gsl(this_patch_histogram, texture_histogram, bins_x, bins_y);
				else if (strcmp(method, "DD") == 0) {
					d[this_iteration] = comp_dd_histogram2d_gsl(this_patch_histogram, texture_histogram, bins_x, bins_y);
				} else {
					printf("Undefined method.\n");
					return 1;
				}

				gsl_histogram2d_reset(this_patch_histogram);		
				this_iteration++;

			}
		}

		gsl_histogram2d_free(this_patch_histogram);
		gsl_histogram2d_free(texture_histogram);

		double best_result;

		if (strcmp(method, "INTERSECT") == 0) 
			best_result = gsl_stats_max(d, 1, num_iterations);
		else if (strcmp(method, "EMDL1") == 0) 
			best_result = gsl_stats_min(d, 1, num_iterations);	
		else if (strcmp(method, "DD") == 0) {
			best_result = gsl_stats_min(d, 1, num_iterations);
		} 

		if (this_texture_num == texture_end_num)
			printf("%.2f", best_result);
		else 
			printf("%.2f,", best_result);


	}

	// free integral histogram

	for (j=0; j<img_input_h+1; j++)
	{
		for (i=0; i<img_input_w+1; i++)
		{
			gsl_histogram2d_free(cum_histograms[((j)*(img_input_w+1)) + i]);	
		}
	}

	//end = clock();
	//double elapsed = ((double) (end - start)) / CLOCKS_PER_SEC;
	//printf("Time elapsed:\t%f\n", elapsed);

}



