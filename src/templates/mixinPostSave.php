foreach (<?= $method ?>('<?= $class ?>:save:post') as $callable) {
    call_user_func($callable, $this, $con, $affectedRows);
}
