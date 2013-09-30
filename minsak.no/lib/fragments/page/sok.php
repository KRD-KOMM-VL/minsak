<?php
/*
Copyright 2013 Kommunal- og regionaldepartementet.

This file is part of minsak.no.

Minsak.no is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 3 as published by the Free Software Foundation.

Minsak.no is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with minsak.no. If not, see http://www.gnu.org/licenses/gpl-3.0.html.


*/
?><?php
/**
 * sok.php
 * Søk
 * 
 * 
 */

global $app;

$statuses = array_flip(Array('all', 'new', 'completed'));

// get variables
$page = array_key_exists('page', $_REQUEST) ? intval($_REQUEST['page']) : 0;
$title = array_key_exists('title', $_REQUEST) ? strval($_REQUEST['title']) : false;
$location_id = array_key_exists('locationId', $_REQUEST) ? $app->stripToNumeric(($_REQUEST['locationId'])) : 0;
$status = array_key_exists('status', $_REQUEST) && array_key_exists($_REQUEST['status'], $statuses) ? strval($_REQUEST['status']) : 'all';
$status_new = ($status == 'new');
$status_completed = ($status == 'completed');
$sort = array_key_exists('sort', $_REQUEST) ? strval($_REQUEST['sort']) : 'age';
if ($sort != 'age' && $sort != 'title' && $sort != 'location_id' && $sort != 'num_votes' && $sort != 'status') {
	$sort = 'age';
}
$sort_dir = (array_key_exists('sort_dir', $_REQUEST) && $_REQUEST['sort_dir'] == 'desc') ? 'desc' : 'asc';  

 
$PERPAGE = 9;


$location = $app->dao->getLocationById($location_id);

$sort_array = Array(
	$sort => $sort_dir
);


// total number available for this query
$num_total = $app->dao->countVisibleInitiativesBySearch($title, $location_id, $status_new, $status_completed);
$initiatives = $app->dao->searchForInitiatives($title, $location_id, $status_new, $status_completed, $sort, $sort_dir, $page * $PERPAGE, $PERPAGE);

// total number returned for this query
$num_result = count($initiatives);

// pages
$num_pages = $num_total/$PERPAGE;
$page_showing_from = $page*$PERPAGE + 1;
$page_showing_to = $page*$PERPAGE + $num_result;

$signatures = $app->dao->getValidSignatureCountsForInitiatives(array_keys($initiatives));

?>
<!-- main -->
<div id="main">
	<!-- content -->
	<div id="content">
        <?php
            echo '<h1>';
            if ($initiatives) {
                echo $app->_('Saker');
                if ($location) {
                    echo ' i ' . $location['name'];
                }
            } else {
                echo 'Søket ditt ga ingen treff';
            }
            echo '</h1>';
        ?>
		<!-- recents -->
		<ul class="recents">
			<?php
			
			foreach ($initiatives as $initiative) {
				$signaturecount = $signatures[$initiative['id']];
				$app->renderFragment('widget/initiative-summary', array('initiative' => $initiative, 'signaturecount' => $signaturecount));
			} 
			
			?>
		</ul>
		
		<!-- paging -->
		<div class="paging">
			<?php
			    if ($num_total > $PERPAGE) {
			        $commonParams = '&title=' . urlencode($title) . '&locationId=' . urlencode($location_id) . '&status=' . urlencode($status) . '&sort=' . urlencode($sort) . '&sort_dir=' . urlencode($sort_dir);
			?>
				
				<?php if ($page > 0) {?>
					<a tabindex="22" href="/sok?page=<?php echo $page-1 . $commonParams; ?>" class="prev">Forrige</a>
				<?php } ?>
				
				<?php if ($page+1 < $num_pages) { ?>
					<a tabindex="23" href="/sok?page=<?php echo $page+1 . $commonParams; ?>" class="next">Neste</a>
				<?php } ?>
				
				<?php if ($num_pages > 1) { ?>
					<ul>
						<?php 
						
						for ($i = 0; $i < $num_pages; $i++) {
							$ip = $i+1;
							if ($page == $i) {
								echo "<li>$ip</li>";
							} else {
								echo "<li><a href=\"/sok?page=$i" . $commonParams . "\">$ip</a></li>";
							}
						}
						
						?>
					</ul>
				<?php } ?>
				<span class="note"><?php echo "(viser $page_showing_from - $page_showing_to av $num_total)"; ?></span>
			<?php } ?>
		</div>
	</div>
	<!-- sidebar -->
	<div id="sidebar">
		<!-- add-nav -->
		<?php $app->renderFragment('widget/menu');?>
		<!-- search-block -->
		<div class="search-block">
			<h2><?php echo $app->_('Søk etter saker'); ?></h2>
            <?php
            $deleters = false;
            if ($title) {
	            if (!$deleters) { echo '<h3>' . $app->_('Du har valgt') . ':</h3>'; }
                $deleters = true;
                $url = '/sok?page=' . $page . '&title=&locationId=' . $location_id . '&status=' . $status . '&sort=' . urlencode($sort) . '&sort_dir=' . urlencode($sort_dir);
                echo '<a href="' . $url . '"><img src="/static/images/x-icon_transparent.png" alt="fjern"/></a> Tittel: ' . htmlspecialchars($title) . '<br/>';
            }
            if ($location) {
            	if (!$deleters) { echo '<h3>' . $app->_('Du har valgt') . ':</h3>'; }
            	$deleters = true;
                $url = '/sok?page=' . $page . '&title=' . urlencode($title) . '&locationId=0&status=' . $status . '&sort=' . urlencode($sort) . '&sort_dir=' . urlencode($sort_dir);
                echo '<a href="' . $url . '"><img src="/static/images/x-icon_transparent.png" alt="fjern"/></a> Kommune/Fylke: ' . htmlspecialchars($location['name']) . '<br/>';
            }
            if ($status_new || $status_completed) {
            	if (!$deleters) { echo '<h3>' . $app->_('Du har valgt') . ':</h3>'; }
            	$deleters = true;
                $url = '/sok?page=' . $page . '&title=' . urlencode($title) . '&locationId=' . $location_id . '&status=all&sort=' . urlencode($sort) . '&sort_dir=' . urlencode($sort_dir);
                if ($status_new) {
                    if ($status_completed) {
                        $statusText = 'Nye & ' . $app->_('Innsendte');
                    } else {
                        $statusText = 'Nye';
                    }
                } else {
                    $statusText = $app->_('Innsendte');
                }
                echo '<a href="' . $url . '"><img src="/static/images/x-icon_transparent.png" alt="fjern"/></a> Status: ' . $statusText . '<br/>';
            }
            if ($sort != 'age') {
            	if (!$deleters) { echo '<h3>' . $app->_('Du har valgt') . ':</h3>'; }
            	$deleters = true;
                $url = '/sok?page=' . $page . '&title=' . urlencode($title) . '&locationId=' . $location_id . '&status=' . $status . '&sort=&sort_dir=' . urlencode($sort_dir);
                switch ($sort) {
                    case 'title':
                        $sortText = 'Sakstittel';
                        break;
                    case 'location_id':
                        $sortText = 'Kommune/fylke';
                        break;
                    case 'num_votes':
                        $sortText = $app->_('Antall underskrifter');
                        break;
                    case 'status':
                        $sortText = 'Status';
                        break;
                }
                echo '<a href="' . $url . '"><img src="/static/images/x-icon_transparent.png" alt="fjern"/></a> Sortering: ' . $sortText . '<br/>';
            }
            if ($sort_dir == 'desc') {
            	if (!$deleters) { echo '<h3>' . $app->_('Du har valgt') . ':</h3>'; }
            	$deleters = true;
                $url = '/sok?page=' . $page . '&title=' . urlencode($title) . '&locationId=' . $location_id . '&status=' . $status . '&sort=' . urlencode($sort) . '&sort_dir=';
                echo '<a href="' . $url . '"><img src="/static/images/x-icon_transparent.png" alt="fjern"/></a> Sortering: Synkende<br/>';
            }
            if ($deleters) {
                echo '<div>&nbsp;</div>';
            }
            ?>
			<form action="/sok">
				<fieldset>
                    <input type="hidden" name="page" value="<?php echo $page; ?>"/>
					<label for="title">Sakstittel</label>
					<div class="row">
						<span class="txt"><input tabindex="29" id="title" type="text" name="title" title="Sakstittel" value="<?php echo htmlspecialchars($title);?>"/></span>
						<input tabindex="30" class="submit" type="submit" value="Søk" />
					</div>
					<label for="county">Kommune/fylke</label>
					<div class="row">
						<span id="search-location-selector-form-container" class="txt-nobackground hidden"></span>
						<?php
						    $data = Array('elementId'=>'search-location-selector-form-container', 'mode' => 1, 'locationId' => $location ? $location['id'] : 0, 'buttonId' => 'search-location-select-button');
						    $app->renderFragment('widget/location-selector', $data);
					    ?>
                        <noscript>
                            <select name="locationId">
                                <option><?php echo $app->_('Velg kommune/fylke'); ?></option>
                                <?php
                                    $counties = $app->dao->getLocationsHierarchy();
                                    foreach ($counties as $county) {
                                        echo '<option value="' . $county['id'] . '"' . ($county['id'] == $location_id ? ' selected="selected"' : '') . '>' . $county['name'] . '</option>';
                                        foreach ($county['children'] as $municipality) {
                                            echo '<option value="' . $municipality['id'] . '"' . ($municipality['id'] == $location_id ? ' selected="selected"' : '') . '>&nbsp;&nbsp;&nbsp;&nbsp;' . $municipality['name'] . '</option>';
                                        }
                                    }
                                ?>
                            </select>
                        </noscript>
						<input id="search-location-select-button" tabindex="32" class="submit choose" type="submit" value="Velg" />
					</div>
					<h3>Status</h3>
					<div class="row2">
						<input tabindex="33" class="checkbox" type="radio" id="new" name="status" value="new" <?php if ($status_new == true) { echo 'checked="checked"'; }?> />
						<label for="new">Nye</label>
					</div>
					<div class="row2">
						<input tabindex="34" class="checkbox" type="radio" id="submitter" name="status" value="completed" <?php if ($status_completed == true) { echo 'checked="checked"'; }?> />
						<label for="submitter"><?php echo $app->_('Innsendte'); ?></label>
					</div>
					<div class="row2 row3">
						<input tabindex="34" class="checkbox" type="radio" id="submitter" name="status" value="all" <?php if ($status == 'all') { echo 'checked="checked"'; }?> />
						<label for="submitter"><?php echo $app->_('Alle'); ?></label>
						<a tabindex="35" href="javascript:document.forms[1].submit();" class="refresh">Oppdater</a>
					</div>
					<h3>Sorter</h3>
                    <div class="row2">
                        <input tabindex="36" class="radio" type="radio" name="sort" id="age" value="age" <?php if ($sort == 'age') { echo 'checked="checked"'; }?> />
                        <label for="age"><?php echo $app->_('Alder'); ?></label>
                    </div>
					<div class="row2">
						<input tabindex="36" class="radio" type="radio" name="sort" id="name" value="title" <?php if ($sort == 'title') { echo 'checked="checked"'; }?> />
						<label for="name"><?php echo $app->_('Sakstittel'); ?></label>
					</div>
					<div class="row2">
						<input tabindex="37" class="radio" type="radio" name="sort" id="local" value="location_id" <?php if ($sort == 'location_id') { echo 'checked="checked"'; }?> />
						<label for="local">Kommune/fylke</label>
					</div>
					<div class="row2">
						<input tabindex="38" class="radio" type="radio" name="sort" id="number" value="num_votes" <?php if ($sort == 'num_votes') { echo 'checked="checked"'; }?> />
						<label for="number"><?php echo $app->_('Antall underskrifter'); ?></label>
					</div>
					<div class="row2 row4">
						<input tabindex="39" class="radio" type="radio" name="sort" id="status" value="status" <?php if ($sort == 'status') { echo 'checked="checked"'; }?> />
						<label for="status">Status</label>
					</div>
					<div class="row2">
						<input tabindex="40" class="radio" type="radio" name="sort_dir" id="ascending" value="asc" <?php if ($sort_dir == 'asc') { echo 'checked="checked"'; }?> />
						<label for="ascending"><?php echo $app->_('Stigende'); ?></label>
					</div>
					<div class="row2">
						<input tabindex="41" class="radio" type="radio" name="sort_dir" id="descending" value="desc" <?php if ($sort_dir == 'desc') { echo 'checked="checked"'; }?> />
						<label for="descending"><?php echo $app->_('Synkede'); ?></label>
						<a tabindex="42" href="javascript:document.forms[1].submit();" class="refresh">Oppdater</a>
					</div>
				</fieldset>
			</form>
		</div>
</div>
	</div>