
###symfony框架命名空间规范及目录说明
```
Symfony\Component 开头的命名空间是Symfony的组件，对应的目录结构： /vendor/{name}/{autoload.psr-0}/代码文件or目录 如 /vendor/symfony/组件名/Symfony/Component/组件名/代码文件or目录
    取决于composer.json的name和autoload配置key

```
