<?php
/**
 * @package Include/help/ja
 */
?>
<h1>サマリ</h1>

エージェントのステータス一覧:

<br><br>

<table width=750px><tr>
	<td class="f9i"><?php print_image("images/status_sets/default/agent_ok.png", false, array("title" => "すべての監視項目が正常", "alt" => "すべての監視項目が正常")); ?></td><td>すべての監視項目が正常</td>
	<td class="f9i"><?php print_image("images/status_sets/default/module_critical.png", false, array("title" => "1つ以上の監視項目で障害", "alt" => "1つ以上の監視項目で障害")); ?></td><td>1つ以上の監視項目で障害</td>
	<td class="f9i"><?php print_image("images/status_sets/default/module_warning.png", false, array("title" => "緑/赤 の状態変化発生", "alt" => "緑/赤 の状態変化発生")); ?></td><td>緑/赤 の状態変化発生</td>

	<td class="f9i"><?php print_image("images/status_sets/default/alert_fired.png", false, array("title" => "アラート発生中", "alt" => "アラート発生中")); ?></td><td>アラート発生中</td>
	<td class="f9i"><?php print_image("images/status_sets/default/alert_disabled.png", false, array("title" => "アラートが無効", "alt" => "アラートが無効")); ?></td><td>アラートが無効</td>

	</tr><tr>

	<td class="f9i"><?php print_image("images/status_sets/default/agent_no_data.png", false, array("title" => "データ収集対象外エージェント", "alt" => "データ収集対象外エージェント")); ?></td><td>データ収集対象外エージェント</td>
	<td class="f9i"><?php print_image("images/status_sets/default/agent_down.png", false, array("title" => "エージェント停止中", "alt" => "エージェント停止中")); ?></td><td>エージェント停止中</td>
	
	<td class="f9i"><?php print_image("images/status_sets/default/alert_not_fired.png", false, array("title" => "アラート未発生", "alt" => "アラート未発生")); ?></td><td>アラート未発生</td>

	</tr>
	</table>
