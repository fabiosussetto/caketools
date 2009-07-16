<?php if ($session->check('Auth.User')): ?>
    <h2>Benvenuto <?php echo $session->read('Auth.User.username'); ?></h2>
    <?php echo $html->link('Logout', array('action' => 'logout')); ?>           
<?php endif; ?>
<?php echo $html->link('Login', array('action' => 'login')); ?> 
<?php echo $html->link('Aggiungi', array('action' => 'add')); ?>
<table>
<tr>
    <th>Id</th>
    <th>Username</th>
    <th>Password</th>
    <th>Email</th>
    <th>Fb_id</th>
    <th></th>
</tr>

<?php foreach ($data as $row): ?>
    <tr>
        <td>
            <?php echo $row['User']['id']; ?>
        </td>
        <td>
            <?php echo $row['User']['username']; ?>
        </td>
        <td>
            <?php echo $row['User']['password']; ?>
        </td>
        <td>
            <?php echo $row['User']['email']; ?>
        </td>
        <td>
            <?php echo $row['User']['fb_id']; ?>
        </td>
        <td>
            <?php echo $html->link('cancella', array('action' => 'delete', $row['User']['id'])); ?>
        </td>
    </tr>
<?php endforeach ?>
</table>