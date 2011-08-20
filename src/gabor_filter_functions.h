/*

construct_gabor_mag_image.h - Gabor filter functions

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

#ifndef GABOR_FILTER_FUNCTIONS_H_
#define	GABOR_FILTER_FUNCTIONS_H_

#include "highgui.h"
#include "cv.h"

IplImage* getGaborMagImg (IplImage*, int, int);

#endif
