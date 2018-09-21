#接口文档

[TOC]

## 约定

### 接口地址

正式服网址：https://airblock.uta0.cn/

测试服网址：https://dev.airblock.uta0.cn/

### 接口调用说明

调用方式：将接口地址作为Pathinfo附在请求地址后，例如

https://airblock.uta0.cn/token/listing

成功请求的时候，输出结果均封装在data字段内，数据格式
```json
    {
        "code": 0,
        "msg": "ok",
        "data": []
    }
```
请求失败的时候返回数据格式
```json
    {
        "code": 401,
        "msg": "用户未登录",
    }
```

## 数据定义

### 全局状态码

|状态码 |描述|
|---|----|
|0      |请求成功|
|401    |用户未登录|
|402    |查询错误|
|403    |表单验证不通过|
|404    |openid获取失败|

### Token状态

值 | 说明
---|---
0 | 进行中，有明确过期时间
1 | 进行中，无明确过期时间
2 | 已结束
3 | 未知

### Token简要信息

| 字段 |              类型    |     描述|
| ---------- |     ---  | ----------|
| id |                int    | token ID |         
| fullname |          string    |  token全称 |  
| abbr |              string    |  名称缩写 |  
| value |             string    |    价值 |  
| status |            int    |   [token状态](#token状态)| 
| expired |            int   |   空投过期时间 | 
| rate |                int  |  项目评分星数 | 
| platform |            string  | 平台 | 
| tokens_per_airdrop |  string  | 每次空投价值 | 
| difficulty_degree |    string |   获取难易度 | 
| logo |                string  |  logo图片url | 


## 相关 API 接口

### 用户登陆

注意：请求任何数据前需要调用一次登录接口，并把返回的PHPSESSID写入cookies再进行请求，否则会提示“用户未登录”

- url

    `/login/weixin`

- method

    `GET`

- input

| 参数名 | 类型 | 是否必须 | 说明  |
| -- | -- | --| -- |
| jscode | string | 是 | 小程序jscode |

- output

| 参数名 | 类型 | 说明  |
| -- | -- | -- |
| phpsessid | string | 会话标识 |
| expire | string | session过期时间 |

```json
{
    "code": 0,
    "data": {
            "phpsessid": "eawefxg4678",// 会话标识
            "expire": "139449333"// 过期时间

    },
    "msg": "ok"
}
```


### token列表

- url

    `/token/listing`

- method

    `GET`

- input

| 参数名 | 类型 | 是否必须 | 说明  |
| -- | -- | --| -- |
| status | int |否| [Token状态定义](#token状态) 全部状态时为 -1|         
| search | string | 否 | 简称或全称关键字 |  
| platform | string|否|     平台名称 |  
| diff |      string|否|    获取难度 |  
| page |      int|否|     页数 |  
| pageSize |  int|否|     每页显示条数 | 
  
- output

| 参数名 | 类型 | 说明  |
| -- | -- | -- |
| list | array json | [Token简要信息](#Token简要信息) |
| count | int | 总记录数 |

```json
{
    "code":0,
    "msg":"ok",
    "data":{
        "list":[
            // token简要信息
        ],
        "count":3244
    }
       
}
```

### token详情列表

- url

    `/token/detail`

- method

    `GET`

- input

| 参数名 | 类型 | 是否必须 | 说明  |
| -- | -- | --| -- |
| id | int | 是 | token id |

- output

| 参数名 | 类型 | 说明  |
| ---------- |         --- |   ----------|
| id |                 int | token ID |         
| fullname |           string |    token全称 |  
| abbr |               string |   名称缩写 |  
| value |              string |        价值 |  
| status |            int  |     [token状态](#token状态) | 
| expired |             int|     空投过期时间 | 
| rate |               int |      项目评分星数 | 
| platform |            string|          平台 | 
| tokens_per_airdrop | string |      每次空投价值 | 
| ticker |              string  |       票据名称 | 
| difficulty_degree |   string |         获取难易度 | 
| logo |               string |         项目logo地址 | 
| to_get_en |         string  |         英文获取途径 | 
| to_get_cn |         string  |           中文获取途径 | 
| information_en |    string  |                  英文项目介绍 | 
| information_cn |    string  |            中文项目介绍 | 
| ico_bench |         string  |       IcoBench地址 | 
| ico_data |           int |        ico日期 | 
| ico_token_price |    string |    ico发行价 |        |
| whitepapers |       string  |        白皮书链接 |


```json
{
    "code":0,
    "msg":"ok",
    "data":{
        // Token详情信息
    }
}
```

### 网页浏览中转

- url

    `/token/webview`

- method

    `GET`

- input

| 参数名 | 类型 | 是否必须 | 说明  |
| -- | -- | --| -- |
| url | string | 是 | 网址 |

- output

```string
直接返回网页html内容
```

### 吐槽

每个用户只能对每个token吐槽一次

- url

    `/complaint/make`

- method

    `GET`

- input

| 参数名 | 类型 | 是否必须 | 说明  |
| -- | -- | --| -- |
| id | int | 是 | token id |
| type | int | 是 | 类型 1-空投已结束 2-链接失效 |
| content | string | 否 | 吐槽内容 |

- output

```json
{
    "code":0,
    "msg":"ok",
}
```

### 联系我们二维码

- url

    `user/linkus`

- method

    `GET`

- input

| 参数名 | 类型 | 是否必须 | 说明  |
| -- | -- | --| -- |

- output

array json
| 参数名 | 类型 | 说明  |
| -- | -- | -- |
| code_name | string | 二维码类型名称，如商务合作、微信公众号 |
| count | string | 二维码图片url |


```json
{
    "code": 0,
    "data": [
        {
            "code_name": "商务合作",
            "value": "http://xxxx" // 二维码url
        },
    ],
    "msg": "ok"
}
```


### 平台名称列表

- url

    `/token/platformList`

- method

    `GET`

- input

| 参数名 | 类型 | 是否可选 | 说明  |
| -- | -- | --| -- |

- output

array json
| 参数名 | 类型|说明|
| ---------- | ----------|----------|
| name | string |   平台名称简称|      
| value | string |  平台全称|


```json
{
    "code": 0,
    "data": {
        "list": [
            {
                "name": "ETH", // 简称
                "value": "Ethereum" // 全称
            }
        ]
    },
    "msg": "ok"
}

```