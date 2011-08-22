/*

get_histogram.h - returns the histogram of a patch

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

*/

#ifndef	GET_HISTOGRAM_H_
#define	GET_HISTOGRAM_H_

#include <stdbool.h>

#include "highgui.h"
#include "cv.h"

#include <gsl/gsl_histogram2d.h>

gsl_histogram2d * get_patch_histogram_through_integral (int, int, int, int, int, int, gsl_histogram2d * [], bool);
gsl_histogram2d * get_patch_histogram (int, int, int, int, IplImage*, gsl_histogram2d *, bool);

#endif
