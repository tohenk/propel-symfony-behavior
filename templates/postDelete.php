foreach (<?= $callables ?>(self::class, ':delete:post') as $callable) {
    call_user_func($callable, $this, $con);
}
