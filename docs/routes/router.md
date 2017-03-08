
#

```
router对象->dispatch(请求对象); //开始路由
    =》过滤事件 router.before
    =>开始路由
        =》根据请求对象匹配路由
        =》触发匹配到的事件 router.matched
        =>
        =>
    =>返回响应对象 Symfony Response类对象
    =》过滤事件 router.after

```
