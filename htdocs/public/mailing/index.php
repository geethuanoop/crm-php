<?php
/* Copyright (C) 2009-2010 Laurent Destailleur  <eldy@users.sourceforge.net>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *     	\file       htdocs/public/members/index.php
 *		\ingroup    core
 *		\brief      A redirect page to an error
 *		\author	    Laurent Destailleur
 *		\version    $Id: index.php,v 1.3 2011/07/31 23:23:21 eldy Exp $
 */

require("../../master.inc.php");

header("Location: ".DOL_URL_ROOT.'/public/error-404.php');

?>
