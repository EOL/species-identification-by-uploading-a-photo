/*

construct_gabor_mag_image.c - Constructs a Gabor magnitude coefficient image of a user defined scale and orientation

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

   > construct_gabor_mag_image

	[/path/to/input_img] 
	[ORIENTATION]
	[SCALE]
	[/path/to/output_img]

2. Returned is a image showing the gabor magnitude coefficients for the given orientation and scale

*/

#include <string.h>
#include <stdlib.h>
#include <stdio.h>
#include <time.h>
#include <math.h>
#include <stdbool.h>
#include <unistd.h>

#include "highgui.h"
#include "cv.h"

#include "gabor_filter_functions.h"

int main(int argc, char **argv) {

	// REALLOCATION OF INPUT VARS
	// ****************************************************

	char *input_img_name	= strdup(argv[1]);
	long int orientation 	= strtol(argv[2], NULL, 0);
	long int scale 		= strtol(argv[3], NULL, 0);
	char *output_img_name	= strdup(argv[4]);

	// PROCESS INPUT IMG
	// ****************************************************

	IplImage* input_img = cvLoadImage(input_img_name, CV_LOAD_IMAGE_COLOR);

	IplImage *output_img = getGaborMagImg(input_img, orientation, scale);
	cvSaveImage(output_img_name, output_img); 

	/*cvNamedWindow("Testing",1);
	cvShowImage("Testing",pImage);
	cvWaitKey(0);
	cvDestroyWindow("Testing");
	cvReleaseImage(&pImage);*/

}
