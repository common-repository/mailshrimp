<?php if (isset($_GET['delete_campaign'])) : ?>
<?php $sdk->campaignDelete($_GET['delete_campaign']); ?>
<?php endif; ?>
<?php $limit = 25; ?>
<?php $page = (isset($_GET['current_page']))?($_GET['current_page']):(0); ?>
<?php $campaigns = $sdk->campaigns(array(), $page, $limit); ?>
<?php $last = floor($campaigns['total'] / $limit); ?>
<div id="campaigns">
	<table width="100%" class="wp-list-table widefat fixed posts">
		<thead>
			<tr>
				<th class="manage-column column-title sortable desc" width="70%">Campaigns (<?= $campaigns['total'] ?>)</th>
				<th class="manage-column column-title sortable desc">Status</th>
				<th class="manage-column column-title sortable desc" width="20%" style="text-align:right;"><a class="big button" href="admin.php?page=mail-shrimp-new-campaign&draft" style="width:64px; display:inline;">Create new</a></th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th class="manage-column column-title sortable desc" colspan="3" style="text-align:left; padding-left:15px">Page
				<?php
					for ($i = 0; $i <= $last; $i++) {
						if ($page != $i)
							echo '<a href="admin.php?page=mail-shrimp&current_page='. $i .'">';
						echo $i + 1 . ' ';
						if ($page != $i)
							echo '</a>';
					}
				?></th>
			</tr>
		</tfoot>
		<tbody id="the-list">
		<?php $i = 0; ?>
		<?php foreach ($campaigns['data'] as $campaign) : ?>
			<tr class="news type-news status-publish hentry iedit author-other <?= ($i++ % 2 == 0)?('alternate'):('') ?>">
				<td class="post-title page-title column-title"><?= $campaign['title'] ?></td>
				<td class="post-title page-title column-title"><?= ucfirst($campaign['status']) ?></td>
				<td class="post-title page-title column-title" style="text-align:right"><a href="admin.php?page=mail-shrimp-new-campaign&campaign_id=<?= $campaign['id'] ?>" class="button-secondary action big">Edit</a>
				<a href="admin.php?page=mail-shrimp&delete_campaign=<?= $campaign['id'] ?>" class="button-secondary action big" onclick="return confirm('Are you sure you want to delete this campaign?');">Delete</a></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
</div>