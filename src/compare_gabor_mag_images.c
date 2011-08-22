/*

compare_gabor_mag_images.c - Compares two Gabor feature vectors

COPYRIGHT (c) 2011 R.M.Barnsley (rmb@astro.ljmu.ac.uk) and Marine 
Biological Lab, Woods Hole, MA.
www.mbl.edu, www.eol.org, https://github.com/EncyclopediaOfLife

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

----------------------------------------------------------------------

NOTES:

1. The binary call takes the form of:

   > compare_gabor_mag_images 

	[SCALES]
	[ORIENTATIONS]
	[GABOR_FEATURE_VECTOR_1]
	[GABOR_FEATURE_VECTOR_2]

2. The input parameters should all be in CSV format.

3. The feature vector is given by [mean_0_0, stddev_0_0, mean_0_1, stddev_1, mean_1_1, stddev_1_1, ... mean_s_o, stddev_s_o]
   where the indices s and o represent the scale and orientation level respectively.

4. Returned is the euclidean distance between the two feature vectors.

TODO:

1. Circular reformatting of the feature vector should be used to ensure rotation invariance, see:

	Zhang, Dengsheng, Aylwin Wong, Maria Indrawan, and Guojun Lu. 2000. Content-based
	Image Retrieval Using Gabor Texture Features. Image Rochester NY 3656 LNCS: 13–15. 
	http://citeseerx.ist.psu.edu/viewdoc/download?doi=10.1.1.63.8420&rep=rep1&type=pdf

*/

#include <string.h>
#include <stdlib.h>
#include <stdio.h>
#include <time.h>
#include <math.h>
#include <stdbool.h>
#include <unistd.h>

#include "compare_gabor_mag_images.h"

int main(int argc, char **argv) {

	// REALLOCATION OF INPUT VARS
	// ****************************************************

	long int scales 	= strtol(argv[1], NULL, 0);
	long int orientations 	= strtol(argv[2], NULL, 0);
	char *f1		= strdup(argv[3]);
	char *f2 		= strdup(argv[4]);

	// SEPARATE SCALES AND ORIENTATIONS INTO ARRAYS
	// ****************************************************

	double f1_vector [scales*orientations*2]; 	// x2 to store both mean and stddev for each scale/orientation
	double f2_vector [scales*orientations*2]; 	// x2 to store both mean and stddev for each scale/orientation

	char* f1_token = strtok(f1, ",");	

	int f1_counter = 0;

	while (f1_token) 
	{
		f1_vector[f1_counter] = strtod(f1_token, NULL);
		f1_token = strtok(NULL, ",");
		f1_counter++;
	}

	char* f2_token = strtok(f2, ",");

	int f2_counter = 0;

	while (f2_token)
	{
		f2_vector[f2_counter] = strtod(f2_token, NULL);
		f2_token = strtok(NULL, ",");
		f2_counter++;
	}

	// SEPARATE MEANS AND STDDEVS INTO SEPARATE ARRAYS
	// ****************************************************

	double f1_mean_vector [scales*orientations];
	double f2_mean_vector [scales*orientations];

	double f1_stddev_vector [scales*orientations];
	double f2_stddev_vector [scales*orientations];

	int ii;

	int element_counter = 0;

	for (ii=0; ii<scales*orientations*2; ii=ii+2) {

		f1_mean_vector[element_counter] = f1_vector[ii];
		f1_stddev_vector[element_counter] = f1_vector[ii+1];

		f2_mean_vector[element_counter] = f2_vector[ii];
		f2_stddev_vector[element_counter] = f2_vector[ii+1];

		element_counter++;

	}

	// GET EUCLIDEAN DISTANCE BETWEEN MEAN/STDDEV VECTOR 
	// PAIRS
	// ****************************************************

	double total_d = 0.0;

	for (ii=0; ii<scales*orientations; ii++) {

		total_d += powf((powf((f1_mean_vector[ii]-f2_mean_vector[ii]),2) + powf((f1_stddev_vector[ii]-f2_stddev_vector[ii]),2)),0.5);
		

	}

	printf("%f\n", total_d);

	return 0;

}
