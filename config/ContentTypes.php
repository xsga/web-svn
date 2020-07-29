<?php
/**
 * WebSVN.
 *
 * This is a fork by xsga of original WebSVN software.
 *
 * WebSVN - Subversion repository viewing via the web using PHP.
 *
 * Copyright (C) 2004-2006 Tim Armes.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License,
 * or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 * or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License
 * for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA.
 *
 * PHP Version 7
 *
 * @author xsga <xsegales@outlook.com>
 * @version 1.0.0
 */

$contentType = array(
        '.dwg'     => 'application/acad', // AutoCAD Drawing files
        '.arj'     => 'application/arj', // ARJ
        '.ccad'    => 'application/clariscad', // ClarisCAD files
        '.drw'     => 'application/drafting', // MATRA Prelude drafting
        '.dxf'     => 'application/dxf', // DXF (AutoCAD)
        '.xls'     => 'application/excel', // Microsoft Excel
        '.unv'     => 'application/i-deas', //SDRC I-DEAS files
        '.igs'     => 'application/iges', // IGES graphics format
        '.iges'    => 'application/iges', // IGES graphics format
        '.hqx'     => 'application/mac-binhex40', // Macintosh BinHex format
        '.word'    => 'application/msword', // Microsoft Word
        '.w6w'     => 'application/msword', // Microsoft Word
        '.doc'     => 'application/msword', // Microsoft Word
        '.wri'     => 'application/mswrite', // Microsoft Write
        '.bin'     => 'application/octet-stream', // Uninterpreted binary
        '.exe'     => 'application/x-msdownload', // Windows EXE
        '.oda'     => 'application/oda', // ODA
        '.pdf'     => 'application/pdf', // PDF (Adobe Acrobat)
        '.ai'      => 'application/postscript', // PostScript
        '.ps'      => 'application/postscript', // PostScript
        '.eps'     => 'application/postscript', // PostScript
        '.prt'     => 'application/pro_eng', // PTC Pro/ENGINEER
        '.part'    => 'application/pro_eng', // PTC Pro/ENGINEER
        '.rtf'     => 'application/rtf', // Rich Text Format
        '.set'     => 'application/set', // SET (French CAD standard)
        '.stl'     => 'application/sla', // Stereolithography
        '.sol'     => 'application/solids', // MATRA Prelude Solids
        '.stp'     => 'application/STEP', // ISO-10303 STEP data files
        '.step'    => 'application/STEP', // ISO-10303 STEP data files
        '.vda'     => 'application/vda', // VDA-FS Surface data
        '.dir'     => 'application/x-director', // Macromedia Director
        '.dcr'     => 'application/x-director', // Macromedia Director
        '.dxr'     => 'application/x-director', // Macromedia Director
        '.mif'     => 'application/x-mif', // FrameMaker MIF Format
        '.csh'     => 'application/x-csh', // C-shell script
        '.dvi'     => 'application/x-dvi', // TeX DVI
        '.gz'      => 'application/gzip', // GNU Zip
        '.gzip'    => 'application/gzip', // GNU Zip
        '.hdf'     => 'application/x-hdf', // ncSA HDF Data File
        '.latex'   => 'application/x-latex', // LaTeX source
        '.nc'      => 'application/x-netcdf', // Unidata netCDF
        '.cdf'     => 'application/x-netcdf', // Unidata netCDF
        '.sit'     => 'application/x-stuffit', // Stiffut Archive
        '.tcl'     => 'application/x-tcl', // TCL script
        '.texinfo' => 'application/x-texinfo', // Texinfo (Emacs)
        '.texi'    => 'application/x-texinfo', // Texinfo (Emacs)
        '.t'       => 'application/x-troff', // Troff
        '.tr'      => 'application/x-troff', // Troff
        '.roff'    => 'application/x-troff', // Troff
        '.man'     => 'application/x-troff-man', // Troff with MAN macros
        '.me'      => 'application/x-troff-me', // Troff with ME macros
        '.ms'      => 'application/x-troff-ms', // Troff with MS macros
        '.src'     => 'application/x-wais-source', // WAIS source
        '.bcpio'   => 'application/x-bcpio', // Old binary CPIO
        '.cpio'    => 'application/x-cpio', // POSIX CPIO
        '.gtar'    => 'application/x-gtar', // GNU tar
        '.shar'    => 'application/x-shar', // Shell archive
        '.sv4cpio' => 'application/x-sv4cpio', // SVR4 CPIO
        '.sv4crc'  => 'application/x-sv4crc', // SVR4 CPIO with CRC
        '.tar'     => 'application/x-tar', // 4.3BSD tar format
        '.ustar'   => 'application/x-ustar', // POSIX tar format
        '.hlp'     => 'application/x-winhelp', // Windows Help
        '.zip'     => 'application/zip', // ZIP archive
        '.au'      => 'audio/basic', // Basic audio (usually m-law)
        '.snd'     => 'audio/basic', // Basic audio (usually m-law)
        '.aif'     => 'audio/x-aiff', // AIFF audio
        '.aiff'    => 'audio/x-aiff', // AIFF audio
        '.aifc'    => 'audio/x-aiff', // AIFF audio
        '.ra'      => 'audio/x-pn-realaudio', // RealAudio
        '.ram'     => 'audio/x-pn-realaudio', // RealAudio
        '.rpm'     => 'audio/x-pn-realaudio-plugin', // RealAudio (plug-in)
        '.wav'     => 'audio/x-wav', // Windows WAVE audio
        '.mp3'     => 'audio/x-mp3', // MP3 files
        '.gif'     => 'image/gif', // gif image
        '.ief'     => 'image/ief', // Image Exchange Format
        '.jpg'     => 'image/jpeg', // JPEG image
        '.jpe'     => 'image/jpeg', // JPEG image
        '.jpeg'    => 'image/jpeg', // JPEG image
        '.pict'    => 'image/pict', // Macintosh PICT
        '.tiff'    => 'image/tiff', // TIFF image
        '.tif'     => 'image/tiff', // TIFF image
        '.ras'     => 'image/x-cmu-raster', // CMU raster
        '.pnm'     => 'image/x-portable-anymap', // PBM Anymap format
        '.pbm'     => 'image/x-portable-bitmap', // PBM Bitmap format
        '.pgm'     => 'image/x-portable-graymap', // PBM Graymap format
        '.ppm'     => 'image/x-portable-pixmap', // PBM Pixmap format
        '.rgb'     => 'image/x-rgb', // RGB Image
        '.xbm'     => 'image/x-xbitmap', // X Bitmap
        '.xpm'     => 'image/x-xpixmap', // X Pixmap
        '.xwd'     => 'image/x-xwindowdump', // X Windows dump (xwd) format
        '.zip'     => 'multipart/x-zip', // PKZIP Archive
        '.gzip'    => 'multipart/x-gzip', // GNU ZIP Archive
        '.mpeg'    => 'video/mpeg', // MPEG video
        '.mpg'     => 'video/mpeg', // MPEG video
        '.mpe'     => 'video/mpeg', // MPEG video
        '.mpeg'    => 'video/mpeg', // MPEG video
        '.qt'      => 'video/quicktime', // QuickTime Video
        '.mov'     => 'video/quicktime', // QuickTime Video
        '.avi'     => 'video/msvideo', // Microsoft Windows Video
        '.movie'   => 'video/x-sgi-movie', // SGI Movieplayer format
        '.wrl'     => 'x-world/x-vrml', // VRML Worlds
        '.ods'     => 'application/vnd.oasis.opendocument.spreadsheet', // OpenDocument Spreadsheet
        '.ots'     => 'application/vnd.oasis.opendocument.spreadsheet-template', // OpenDocument Spreadsheet Template
        '.odp'     => 'application/vnd.oasis.opendocument.presentation', // OpenDocument Presentation
        '.otp'     => 'application/vnd.oasis.opendocument.presentation-template', // OpenDocument Presentation Template
        '.odg'     => 'application/vnd.oasis.opendocument.graphics', // OpenDocument Drawing
        '.otg'     => 'application/vnd.oasis.opendocument.graphics-template', // OpenDocument Drawing Template
        '.odc'     => 'application/vnd.oasis.opendocument.chart', // OpenDocument Chart
        '.otc'     => 'application/vnd.oasis.opendocument.chart-template', // OpenDocument Chart Template
        '.odf'     => 'application/vnd.oasis.opendocument.formula', // OpenDocument Formula
        '.otf'     => 'application/vnd.oasis.opendocument.formula-template', // OpenDocument Formula Template
        '.odi'     => 'application/vnd.oasis.opendocument.image', // OpenDocument Image
        '.oti'     => 'application/vnd.oasis.opendocument.image-template', // OpenDocument Image Template
        '.odb'     => 'application/vnd.oasis.opendocument.database', // OpenDocument Database
        '.odt'     => 'application/vnd.oasis.opendocument.text', // OpenDocument Text
        '.ott'     => 'application/vnd.oasis.opendocument.text-template', // OpenDocument Text Template
        '.odm'     => 'application/vnd.oasis.opendocument.text-master', // OpenDocument Master Document
        '.oth'     => 'application/vnd.oasis.opendocument.text-web' // OpenDocument HTML Template
);
