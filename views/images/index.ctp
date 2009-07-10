<?php echo $javascript->link(array('/editor/jquery.wysiwyg'), false); ?>
<?php echo $html->css(array('/editor/jquery.wysiwyg'), null, null, false); ?>

<script type="text/javascript" charset="utf-8">
    $(function(){
        $('#editor').wysiwyg();
    });
</script>

<?php echo $form->textarea('Test.field', array('id' => 'editor')); ?>



<table>
    <?php echo $html->tableHeaders(array('id', 'titolo', 'anteprima', 'creata il', '')); ?>
    <?php foreach ($data as $row): ?>
        <tr>
            <td><?php echo $row['Image']['id']; ?></td>
            <td><?php echo $row['Image']['title']; ?></td>
            <td><?php echo $html->image($row['Image']['Versions']['thumb']['filename']); ?></td>
            <td><?php echo $row['Image']['created']; ?></td>
            <td><?php echo $html->link('cancella', array('controller' => 'controller', 'action' => 'action')); ?></td>
        </tr>
    <?php endforeach ?>
</table>