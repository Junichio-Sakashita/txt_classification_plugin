# 文書分類プラグイン#

This plugin classifies the contents of comment sheets in moodle into one of "Question", "Comment", "Request" or "Other" using GPT API.

After logging in to moodle, the user accesses the course where the comment sheet has been set up. 
Then, select "Reports" in the index list, and choose the plugin for classifying documents (named "Document Classification System"). 
Then, a list of comment sheets in the course, a text box to input the GPT API key, and a pull-down menu to select a model are displayed. 

After that, the response data and the classification labels are displayed, and the user can narrow down the labels to be displayed if necessary. 
In the classification screen, the name of the lecture (which corresponds to the section name in moodle), the question on the comment sheet, the respondent's ID, and, in the case of a multi-line comment sheet, the respondent's subID to identify the comment sheet as having been answered by the same person, since the document is split up in the preprocessing step. 
The label of the comment sheet is displayed in the last column.

## Installing via uploaded ZIP file ##

1. Log in to your Moodle site as an admin and go to _Site administration >
   Plugins > Install plugins_.
2. Upload the ZIP file with the plugin code. You should only be prompted to add
   extra details if your plugin type is not automatically detected.
3. Check the plugin validation report and finish the installation.

## Installing manually ##

The plugin can be also installed by putting the contents of this directory to

    {your/moodle/dirroot}/report/test

Afterwards, log in to your Moodle site as an admin and go to _Site administration >
Notifications_ to complete the installation.

Alternatively, you can run

    $ php admin/cli/upgrade.php

to complete the installation from the command line.

## License ##

2023 Junichiro Sakashita <m221334@hiroshima-u.ac.jp>

This program is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with
this program.  If not, see <https://www.gnu.org/licenses/>.
