foreach (<?= $callables ?>(self::class, ':delete:pre') as $callable) {
    if (call_user_func($callable, $this, $con)) {
        $con->commit();

        return;
    }
}
