<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package   qformat_qti12
 * @author    Christoph Jobst <cjobst@wifa.uni-leipzig.de>
 * @copyright 2019, University Leipzig
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->component = 'qformat_qti12';

$plugin->version    = 2019021200;
$plugin->requires   = 2018051704; // 3.5.4
$plugin->release    = '0.1dev (Build: 20190212)';
$plugin->maturity   = MATURITY_ALPHA;