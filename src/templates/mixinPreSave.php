foreach (<?= $method ?>('<?= $class ?>:save:pre') as $callable) {
    if (is_integer($affectedRows = call_user_func($callable, $this, $con))) {
        $con->commit();

        return $affectedRows;
    }
}
