example



login:
{
    "headers":{},
    "body":{
        "method":"auth", 
        "model": "login",
        "data": {"id":"example@mail.com", "password":"j8932y889u23"}
    }
}

(NOTE: 因为一个userId，可以绑定多个Client id，解绑后，可能一个浏览器多个tab，使用的是不同的client id，需要检查是否在当前浏览器完全退出)
logout:
{
    "headers":{"token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiIsImp0aSI6ImxPOFNVblFYelhvRll5aStMXC9rTDl4MHNcL0trRURjVkREeFFwUXJSYkplTjhGckRQc3oxakwwYlwvcG5sUFwvUEhNUmJMSDZjQ1l1NFFMdXVWUjc5RjBxakRsXC84RFNxcVRwVUQ5cDZ3TW9qZ2Q4ZEh2NWhKdk5EazJ6a2NROGlTNEwifQ.eyJpc3MiOiJsZXNzY2xvdWQiLCJhdWQiOiJhcHAiLCJqdGkiOiJsTzhTVW5RWHpYb0ZZeWkrTFwva0w5eDBzXC9La0VEY1ZERHhRcFFyUmJKZU44RnJEUHN6MWpMMGJcL3BubFBcL1BITVJiTEg2Y0NZdTRRTHV1VlI3OUYwcWpEbFwvOERTcXFUcFVEOXA2d01vamdkOGRIdjVoSnZORGsyemtjUThpUzRMIiwiaWF0IjoxNTE1MjYxNDgwLCJuYmYiOjE1MTUyNjE0ODAsImV4cCI6MTU0Njc5NzQ4MCwidXNlcklkIjoiNWE0NjY0MWZlYTkzMGYzZDk4MGFhMjFmIiwicm9sZSI6Im1lbWJlciJ9.5wBSWpa52JrXoVrkDAZtw3aBsZRfVoGXjRxXAW25jc4"},
    "body":{
        "method":"auth", 
        "model": "logout"
    }
}

(when the client has been logged in and got token, but websocket has been disconnectted, then just set the token to headers and rebind userId with client id)
auth:
{
    "headers":{"token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiIsImp0aSI6ImxPOFNVblFYelhvRll5aStMXC9rTDl4MHNcL0trRURjVkREeFFwUXJSYkplTjhGckRQc3oxakwwYlwvcG5sUFwvUEhNUmJMSDZjQ1l1NFFMdXVWUjc5RjBxakRsXC84RFNxcVRwVUQ5cDZ3TW9qZ2Q4ZEh2NWhKdk5EazJ6a2NROGlTNEwifQ.eyJpc3MiOiJsZXNzY2xvdWQiLCJhdWQiOiJhcHAiLCJqdGkiOiJsTzhTVW5RWHpYb0ZZeWkrTFwva0w5eDBzXC9La0VEY1ZERHhRcFFyUmJKZU44RnJEUHN6MWpMMGJcL3BubFBcL1BITVJiTEg2Y0NZdTRRTHV1VlI3OUYwcWpEbFwvOERTcXFUcFVEOXA2d01vamdkOGRIdjVoSnZORGsyemtjUThpUzRMIiwiaWF0IjoxNTE1MjYxNDgwLCJuYmYiOjE1MTUyNjE0ODAsImV4cCI6MTU0Njc5NzQ4MCwidXNlcklkIjoiNWE0NjY0MWZlYTkzMGYzZDk4MGFhMjFmIiwicm9sZSI6Im1lbWJlciJ9.5wBSWpa52JrXoVrkDAZtw3aBsZRfVoGXjRxXAW25jc4"},
    "body":{
        "method":"auth", 
        "model": "user"
    }
}

query:
{
    "headers":{},
    "body":{
        "method":"read", 
        "model": "order",
        "query":{
            "$where": {"status":"pending"}, 
            "fields": {"status":"test"},
            "$groupBy": "group", 
            "$orderBy":"id"
        }
    }
}

create:
{
    "headers":{},
    "body":{
        "method":"create",
        "model": "order",
        "data":{"attr": "value"}
    }
}

update:
{
    "headers":{},
    "body":{
        "method":"update",
        "model": "order",
        "query": {
            "$where": {"userId":111},   // userId must be defualt condition
        },
        "data":{"attr": "value"}
    }
}


delete:
{
    "headers":{},
    "body":{
        "method":"delete", // could be use the deleteAll
        "model": "comment",
        "query": {
            "$where": {"id":1111},
        }
    }
}



methods: Find[One], FindAll, Delete[One], DeleteAll, Create[One], CreateAll, Update[One], UpdateAll

$conditionMarks = [
        '$group'=>'parseWithJSON',
        '$order'=>'parseWithJSON',
        '$where'=>'parseWithJSON',
        '$skip'=>'parseWithInt',
        '$limit'=>'parseWithInt',
        '$count'=>'parseWithJSON',
        '$fields'=>'parseConditionFields',
        '$in'=>'parseConditionIn',
        '$relation'=>'parseWithJSON',
        '$fuzzy'=>'parseWithFuzzy'
    ];

查询条件 ->
                        支持以下查询方式:
                            $group
                            $order
                            $where
                            $skip
                            $limit
                            $count
                            $fields
                            $in
                            $relation
                            $fuzzy

    // ['$lt','$lte','$gt','$gte','$ne','$in','$nin','$exists','$select','$dontSelect','$all'];


        '$group': {"id","group"},
        '$order': {"id","createdAt"},
        '$skip': 0,
        '$limit': 100,
        '$count': {"*"},
        '$count': {"field"},
        '$fields'{"field1","field2"},

        '$where':{"status":"normal"},

<!-- 以下暂不支持 -->
        '$in':{"field":["1","2","3"]},
        '$nin':{"field":["4","5","6"]},
        '$fuzzy':{"field":"qqq%", "field2":"%qqq", "field3":"%qqq%"}
        '$or': {"field1":1, "field2":2}

默认条件 -> 
                        用ROOT-Secret不应用默认条件
                        默认条件需要与指定条件merge

权限         ->
                        模型粗粒度验证，例如 guest用户组不能访问某个模型
                        ACL验证权限


验证         ->
                        验证属性需满足一定的规则，比如不能为空，或必须是数字

事件         ->
                        在模型处理前后，如果有需要可以在前后注册事件，例如，保存数据前执行某个方法，保存数据后执行某个方法

过滤         ->
                        根据权限设置，某些属性对客户端不可见

默认值         ->
                        属性包括，一般默认值，和强制默认值，一般默认值可以被覆盖，强制默认值不能被覆盖，只能被修改












query:

{
    "headers":{},
    "body":{
        "method":"read", 
        "model": "wallet",
        "query":{
            "$where": {"userId":111}, 
            "fields": ["userId","amount"],
            "$orderBy":"id"
        }
    }
}

{
    "headers":{},
    "body":{
        "method":"read", 
        "model": "wallet",
        "query":{
            "$where": {"userId":111}, 
            "fields": ["userId","amount"]
        }
    }
}





{
    "headers":{},
    "body":{
        "method":"read", 
        "model": "wallet",
        "query":{
            "$where": {"userId":"111"}
        }
    }
}




create

{
    "headers":{},
    "body":{
        "method":"create", 
        "model": "wallet",
        "data":{
"id":"12",
"userId":111,
"coin":"eth",
"amount":1,
"freeze":11,
"signature":"123"
        }
    }
}





{
    "headers":{},
    "body":{
        "method":"create", 
        "model": "wallet",
        "data":{
"id":"1099",
"userId":19911,
"coin":"e9th9",
"amount":1,
"freeze":11,
"signature":"123"
        }
    }
}



update

{
    "headers":{},
    "body":{
        "method":"update", 
        "model": "wallet",
        "query":{
            "$where":{"id":10993}
        },
        "data":{
"amount":1,
"freeze":"11zzz",
"signature":"123"
        }
    }
}






{
    "headers":{},
    "body":{
        "method":"readAll", 
        "model": "wallet",
        "query":{
            "$where": {},
"$page":1,
"$limit":3,
"$order":{"id":"asc"}
        }
    }
}



##返回结果

创建

	{"method":"create", "results":"xxx"}
	xxx 表示插入数据的id
	
更新

	{"method":"update", "results":xxx}
	xxx 表示更新数据影响了多少行

删除

	
	{"method":"delete","results":xxx}
	xxx 表示删除数据的id
