/*

gabor_filter_functions.c - Gabor filter functions

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

#include <string.h>
#include <stdlib.h>
#include <stdio.h>
#include <time.h>
#include <math.h>
#include <stdbool.h>

#include "highgui.h"
#include "cv.h"

#include "cvgabor.h"

#include "gabor_filter_functions.h"

IplImage* getGaborMagImg (IplImage* img, int orientation, int scale) {
                       
	double Sigma = 2*PI;
        double F = sqrt(2.0);
        CvGabor *gabor = new CvGabor;
        gabor->Init(orientation, scale, Sigma, F);

	// convert to grayscale image
	IplImage *img_gray = cvCreateImage(cvGetSize(img), img->depth, 1);		
	cvCvtColor(img, img_gray, CV_RGB2GRAY);						

	// create gabor magnitude image
	IplImage* img_conv = cvCreateImage(cvGetSize(img), img->depth, 1);		
        gabor->conv_img(img_gray, img_conv, CV_GABOR_MAG);					

        free(gabor);

	return img_conv;

}
