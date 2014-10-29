<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.View.Layouts
 * @since         CakePHP(tm) v 0.10.0.1076
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

$cakeDescription = __d('cake_dev', 'CakePHP: the rapid development php framework');
$cakeVersion = __d('cake_dev', 'CakePHP %s', Configure::version())
?>
<!DOCTYPE html>
<html>
<head>
	<?php echo $this->Html->charset(); ?>
	<title>
		<?php echo $cakeDescription ?>:
		<?php echo $this->fetch('title'); ?>
	</title>
	<?php
		echo $this->Html->meta('icon');

		echo $this->Html->css('cake.generic');

		echo $this->fetch('meta');
		echo $this->fetch('css');

		echo $this->Html->script( 'vendor.js');
		echo $this->Html->script( 'vendor/modernizr.js');
		echo $this->Html->script( 'plugins.js');
		echo $this->Html->script( 'main.js');

		echo $this->fetch('script');
?>
</head>
<body>
	<div id="container">
		<div id="header">
			<h1><?php echo $this->Html->link($cakeDescription, 'http://cakephp.org'); ?></h1>
		</div>
		<div id="content">

			<?php echo $this->Session->flash(); ?>

			<div id="filter"></div>
			<div id="result"></div>

		</div>
		<div id="footer">
			<?php echo $this->Html->link(
					$this->Html->image('cake.power.gif', array('alt' => $cakeDescription, 'border' => '0')),
					'http://www.cakephp.org/',
					array('target' => '_blank', 'escape' => false, 'id' => 'cake-powered')
				);
			?>
			<p>
				<?php echo $cakeVersion; ?>
			</p>
		</div>
	</div>
/*	<?php echo $this->element('sql_dump'); ?> */


	<script id="SearchViewTemplate" type="text/html">
		<input type="button" id="sub" value="sub" />
		<table>
		<tbody>
		<tr>
		<td><input type="button" id="add" value="add" /></td>
		<td><input type="button" id="addAjax" value="addAjax" /></td>
		</tr>
		<tr>
		<td><input type="button" id="edit" value="edit" /></td>
		<td><input type="button" id="editAjax" value="editAjax" /></td>
		</tr>
		<tr>
		<td><input type="button" id="delete" value="delete" /></td>
		<td><input type="button" id="deleteAjax" value="deleteAjax" /></td>
		</tr>
		<tr>
		<td><input type="button" id="view" value="view" />
		<td><input type="button" id="viewAjax" value="viewAjax" />
		</tr>
		<tr>
		<td><input type="button" id="index" value="index" />
		<td><input type="button" id="indexAjax" value="indexAjax" />
		</tr>
		</tbody>
		</table>
		<div id="eventName"></div>
		<table>
		<thead>
		</thead>
		<tbody id="body">
		</tbody>
		</table>
	</script>

	<script id="ItemViewTemplate" type="text/html">
		<td id="id"><%- id %></td>
		<td id="username"><%- username %></td>
		<td id="password"><%- password %></td>
		<td id="role"><%- role %></td>
		<td id="created"><%- created %></td>
		<td id="modified"><%- modified %></td>
	</script>
	<?php echo $this->element('sql_dump'); ?>

</body>
</html>
