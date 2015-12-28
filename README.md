# e-fw

eason's php framework，全称为Eason_Framework?（简称：E_FW，暂定名）。

> 这个框架是基於多年的實戰经验所成。它不是为了提升网站的性能，是为了提升團隊的开发效率以及產出的代碼質量及性能。因为一个好的框架，能使一班平庸的开发者在没有领导的情况下，开发出高于整體平均水平线上的产品。

它主要的特性包括有：
* 單一入口模式，智能路由；
* 基本的MVC分層；
    * 入口（index） > 控制器/業務邏輯（controller） > 數據邏輯（model） > tablegateway
* 智能的數據表映射，完善的CRUD操作；自適應分佈式數據庫功能，能從根本上避免由於實戰經驗不足而導致的慢查詢情況；自帶數據緩存；
* 功能強大而又簡單方便的數據校驗及過濾功能，並且非常容易的二次開發；
* 無縫結合多種模板庫（phplib、smarty）
* 智能緩存，適配多種存儲方式及多種緩存級別；
* 日誌，方便各種監控、分析；
