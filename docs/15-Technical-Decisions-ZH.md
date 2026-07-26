# PantryFlow 技术决策记录（中文版）

**文档状态：** 已接受，和当前实现一致

**最后更新：** 2026 年 7 月 26 日

**适用范围：** PantryFlow WAP Final Assessment

## 1. 这份文档解决什么问题

需求文档说明系统“要做什么”，架构图说明组件“怎样连接”，而这份记录只回答一个更难也更有价值的问题：**为什么这样做，以及我们愿意承担什么代价。**

PantryFlow 的技术目标并不是把两张表包装成一套伪企业系统，而是在课程限定的 HTML、CSS、JavaScript、PHP、PDO 与 MySQL 技术栈里，把几个真正容易出错的地方处理扎实：库存不能扣成负数、重复拒绝不能重复补货、普通访客不能读取别人的联系方式、管理员操作不能只靠隐藏按钮来保护。

因此，以下选择以三项原则排序：先满足 assessment rubric，再保证数据状态可靠，最后才是界面表现和扩展性。

## 2. 决策摘要

| ID | 决策 | 当前状态 | 主要对应范围 |
|---|---|---|---|
| TD-01 | 保持课程指定的原生 PHP 技术栈，不引入框架 | 接受 | A1-A5、C1 |
| TD-02 | 使用浏览器、PHP、MySQL 三层 client-server 结构 | 接受 | C1、C2 |
| TD-03 | 数据库只保留 `food_items` 与 `client_requests` 两个核心实体 | 接受 | B1 |
| TD-04 | 请求建立与库存扣减必须是一个加锁事务 | 接受 | A3、B2 |
| TD-05 | 使用 Post/Redirect/Get 和短生命周期提示消息 | 接受 | A3、NFR |
| TD-06 | 访客历史只在 Session 留入口，业务记录仍永久写入数据库 | 接受 | A3、A5、Privacy |
| TD-07 | 按 rubric 使用 hardcoded admin，但把权限边界做完整 | 接受但限于 assessment | A4 |
| TD-08 | 采用浏览器、PHP、数据库三层验证与约束 | 接受 | A2、A3、B2 |
| TD-09 | 管理操作采用 reject、archive、restore 和受限 delete 的生命周期 | 接受 | A5、NFR |
| TD-10 | Public UI 与 Operations UI 使用不同信息密度 | 接受 | HCI、C4 |
| TD-11 | URL 不写死 8080，兼容老师的标准 80 端口 | 接受 | Deployment |
| TD-12 | 对用户显示安全错误，对开发端保留诊断信息 | 接受 | Security、Maintainability |
| TD-13 | 明确保留生产化缺口，不用“assessment 已完成”掩盖风险 | 接受 | C1、C4 |

## 3. 详细技术决策

### TD-01：原生 PHP，而不是为了显得高级引入框架

**决定。** 前端使用语义化 HTML、CSS 与 vanilla JavaScript；服务器使用 PHP；数据库访问统一经由 PDO；数据储存在 MySQL/MariaDB。

**原因。** 这是课程、题目与 marking rubric 共同指向的技术边界。Laravel、React 或 Node.js 并不能提高本次评分依据，反而会把路由、验证和数据库事务藏进框架约定，使老师更难直接检查关键能力。原生 PHP 的缺点是重复工作较多，但对于这个体量，它让请求的每一步都可见：输入从哪里来、何时验证、哪一条 SQL 改了状态、失败后如何回滚。

**没有选择。** SPA、ORM、服务拆分和依赖注入容器。它们不是坏技术，只是与这个问题的规模和教学目标不相称。

**代价。** 路由与模板组合较手工，长期扩展到多角色、多地点或大量表单时会开始吃力。当前通过共享 `includes/`、集中 PDO 配置与清晰的 action 文件控制复杂度。

### TD-02：三层 client-server，但不制造多余的“层”

**决定。** 浏览器负责呈现和即时交互；Apache/PHP 负责可信验证、认证、业务规则与 HTML response；MySQL 负责持久状态、关系约束和事务。

**原因。** 权限和库存变化不能由浏览器决定。浏览器可以提示日期无效，却不能证明日期有效；JavaScript 可以限制 quantity input，却挡不住手工 POST。真正的状态转换必须回到 PHP，再由数据库完成最后的完整性保护。

这个边界也让报告里的 architecture diagram 不是装饰：每一层都有明确责任，而且可以说明自己**不应该**做什么。例如 MySQL 不负责写用户提示，CSS 也不决定一个 item 是否仍可请求。

**代价。** 当前采用 server-rendered pages，每次主要操作会进行完整页面请求，不具备 SPA 的局部更新感。但本项目的数据量和使用频率很低，简单、可调试和可评分比减少一次页面刷新更重要。

### TD-03：两张核心表是有意简化，不是 ERD 漏画

**决定。** `food_items` 保存库存；`client_requests` 保存一次只选择一个 food item 的请求。关系是一个 food item 对零到多个 client requests。

**原因。** 题目没有用户注册，也明确要求 hardcoded administrator login。为此增加 `users`、`admins`、`roles` 会产生没有真实业务来源的实体。一次请求也只包含一个 item，所以再增加 `request_items` 中间表只会把简单的一对多伪装成购物车模型。

**关键完整性。** `client_requests.food_item_id` 是 foreign key，`ON DELETE RESTRICT` 阻止删除已有历史引用的 food item；pickup date、status、quantity 和常用查询字段具有相应约束或索引。

**何时应改变。** 如果需求变成一次领取多种食物，才应拆成 `requests` 与 `request_items`；如果管理员不再是课程要求的单一账号，才应增加用户、角色和凭证模型。

### TD-04：库存移动必须是 transaction，不接受“先查再改”的侥幸

**决定。** 创建请求时执行以下原子流程：开始事务，`SELECT ... FOR UPDATE` 锁定目标库存行，重新检查 active、expiry 与 quantity，插入 request，再以 guard condition 扣减库存，最后 commit；任一步失败则 rollback。

**原因。** 最危险的 bug 不是 SQL 报错，而是两个请求都读到“还剩 1 个”，随后都成功。单纯在页面加载时检查库存无法防止这个 race condition。行锁使检查与更新发生在同一串行化窗口内，而 guarded update 再次保证 quantity 不会越过零。

**不变量。** 数据库中不允许出现“request 已建立但库存没扣”，也不允许“库存已扣但 request 不存在”。这比成功提示的文案更重要，因为界面可以重画，账不能靠猜。

**代价。** 加锁会短暂降低同一 item 的并发吞吐量。对于社区 pantry 的流量，这是正确取舍；如果未来进入高并发场景，再考虑 reservation queue 或事件化库存，而不是先放弃一致性。

### TD-05：成功后 redirect，提示消息不长期霸占页面

**决定。** 写操作完成后使用 Post/Redirect/Get。成功或失败消息通过 Session flash data 传递一次，读取后即清除。

**原因。** 用户刷新 confirmation page 时不应重复提交同一个 POST。PRG 把“改变状态”和“显示结果”拆开，浏览器刷新的是 GET。Flash message 只负责当次反馈，不应像数据库记录一样永久挂在 operations workspace 顶部。

**结果。** 这同时解决重复提交和 UI 噪音问题。需要长期查阅的内容进入 My pickups 或管理员表格，不靠横幅冒充 history。

### TD-06：关闭浏览器可清空访客入口，但不能清空管理员数据

**决定。** Public 端的 My pickups 不建立客户账号。Session 只保存当前浏览器本次会话创建过的 request IDs；Session cookie 使用 lifetime `0`，正常关闭浏览器后即不再保留。真实的 request、联系方式、pickup date 和库存变化写入 MySQL，并继续出现在管理员端。

**原因。** 这把“方便访客继续完成当前 journey”和“把所有客户记录公开成 history”区分开。Session ID 只是访问索引，不是业务真相。即使访客 cookie 消失，管理员仍需处理已经发生的 pickup request，因此数据库记录不能跟着删除。

**隐私边界。** Public confirmation 必须先检查目标 request ID 是否属于当前 Session，不能因为猜到 URL 参数就返回别人资料。管理员 dashboard 则必须经过 authenticated Session guard。

**代价。** 访客换浏览器、换设备或清除 cookie 后无法自行找回历史。这是没有用户账号情况下的有意限制，不应通过公开搜索 request ID 来“修复”。

### TD-07：hardcoded login 是题目约束，不是生产身份系统

**决定。** 保留 assessment 指定的 `pantry_admin` / `help2026`，但凭证只放在集中配置中；成功登录后 regenerate Session ID；所有 protected routes 与 action scripts 重复执行权限 guard；logout 使用 POST 并移除管理员认证状态。

**原因。** Rubric 要检查 hardcoded username/password、PHP Session 与 logout。做一张 admin table 反而偏离要求。真正要证明的能力不是“账号从哪张表来”，而是认证成功后是否形成一致的服务器权限边界。

**风险。** 明文凭证不能用于互联网生产环境。正式版本应迁移到 `password_hash` / `password_verify`、数据库账号或外部 identity provider，并配合 HTTPS、rate limiting 和 credential rotation。

### TD-08：验证采用三层防线，各层职责不同

**决定。** JavaScript 提供及时、字段级 feedback；PHP 完整重复业务验证；数据库用类型、foreign key、check constraint 与 transaction 保护最终状态。外部值进入 SQL 时使用 PDO prepared statements，动态输出进入 HTML 时统一 escaping。

**原因。** “前端已经 required”不是安全控制，“用了 prepared statement”也不等于解决 XSS。SQL injection 与 output injection 属于不同边界：前者靠参数化查询保护 SQL 结构，后者靠适合 HTML context 的编码保护页面。

**错误处理。** 用户只收到可行动但不泄漏 schema、path 或 stack trace 的消息；技术异常写入 server log。这样既不让错误变成信息泄露，也不让开发者失去排查线索。

### TD-09：Reject 不是 Delete，Archive 也不是 Delete

**决定。** Pending request 被拒绝时保留原记录，把 status 改为 `rejected`，写入 reviewed time，并在同一事务中恢复 quantity。Food item 默认只做 archive/restore；永久 delete 只有在 item 已 archived 且 request reference count 为零时才允许。

**原因。** 删除请求会抹掉为什么库存发生变化；直接删除 food item 会破坏历史关系。更重要的是，重复点击 reject 不能重复加库存。因此 rejection 的合法状态转换只有 `pending -> rejected`，已经 rejected 的请求再次提交必须是 no-op。

**双重保护。** PHP 先检查状态与引用数，数据库 foreign key 再做最后一道防线。UI 中不显示危险操作并不构成安全；服务器仍会拒绝手工伪造的非法 POST。

**代价。** 当前没有 approved、collected、cancelled 等完整 fulfilment workflow。题目没有要求这些状态，硬加会让 demo 和报告变散。若未来需要运营追踪，应增加明确状态机和 audit actor，而不是继续堆布尔字段。

### TD-10：Public 端要安静，Operations 端要高密度

**决定。** Public 体验采用较宽呼吸感、少量主动作和清晰 step flow；管理员 Operations 使用表格、紧凑指标和左右工作区，让 Upcoming requests、All food items 与 Low stock 在较少滚动内可比较。

**原因。** 两类用户的任务不同。访客可能紧张、低频使用，不应该先理解后台概念；管理员是重复工作，需要扫描、比较和执行。把两边都做成大卡片会显得“漂亮但慢”，把两边都做成密集表格则会牺牲公共服务的可接近性。

视觉语言借用了 hospitality service 的克制感，但没有照搬酒店 booking flow。品牌感只服务于 hierarchy、trust 和 readability；不在 footer 展示 PHP、PDO、assessment 等开发信息，也不使用无意义的状态图例制造“AI dashboard 感”。

### TD-11：端口属于部署环境，不属于业务代码

**决定。** 页面链接使用相对路径，不在 PHP/HTML 中写死 `localhost:8080`。本机 Apache 可通过 `http://localhost:8080/pantryflow/` 访问，老师使用标准 80 端口时通过 `http://localhost/pantryflow/` 访问。

**原因。** 8080 与 80 只是 Apache listening port 的差异，不应该产生两套代码。README 说明两种 URL，应用内部继续使用相对导航。

**结果。** 项目复制到老师的 XAMPP `htdocs` 后不需要为了端口修改源码，降低 demonstration 与 marking 环境不一致的风险。

### TD-12：错误信息既不能沉默，也不能泄密

**决定。** 用户界面提供明确的 field error、transaction result 与 protected-access feedback；PDO 使用 exception mode；底层异常记录到 server log，对外只显示稳定、非技术性的错误说明。

**原因。** 完全吞掉错误会让库存问题无法诊断，直接输出 exception 又可能暴露 SQL、目录或配置。两者之间的合理边界是：用户知道下一步能做什么，开发者在日志中知道具体哪里坏了。

**当前限制。** 这是单机 XAMPP assessment，没有集中日志、metrics 或 alerting。正式部署至少应加入 request correlation ID、结构化日志、失败率监控和数据库备份验证。

### TD-13：明确 production gap，比假装“企业级”更专业

当前实现满足 assessment，但不等同于可以直接开放公网。下列差距被明确保留：

- 管理员写操作尚未加入 CSRF token。
- Hardcoded password 必须改为 hash-based identity storage。
- 部署时必须启用 HTTPS，并根据环境强化 cookie `Secure` 属性。
- 登录没有 rate limiting 或 lockout。
- 请求资料没有 retention、anonymisation 与正式 audit policy。
- Request lifecycle 只覆盖 `pending` 与 `rejected`，没有完成领取状态。

如果继续迭代，优先顺序应是 **CSRF 与真实身份管理 -> 状态审计与数据保留 -> 自动化测试 -> 可观察性 -> 功能扩张**。先加购物车、邮件或漂亮图表并不会修复现有安全边界，因此不应排在前面。

## 4. 决策之间怎样连起来

这些决定不是各自独立的小技巧，而是一条连续的状态保护链：

1. Public UI 让访客选对 item、quantity 与 date。
2. JavaScript 尽早指出明显错误，但不拥有最终决定权。
3. PHP 重新验证身份、输入和当前业务状态。
4. PDO prepared statements 隔离数据与 SQL 语法。
5. InnoDB transaction 和 row lock 保护库存不变量。
6. Foreign key、constraint 与 lifecycle state 阻止历史被无意破坏。
7. PRG 和 Session 只解决 interaction continuity，不取代数据库持久化。
8. Admin workspace 把这些状态紧密呈现，让人可以高效处理而不是猜测。

所以，PantryFlow 的核心设计不是绿色配色或两张 database table，而是：**任何会改变库存或历史的动作，都必须由服务器验证，并且以可回滚、可解释、不可重复获利的方式落库。**

## 5. 与 assessment rubric 的直接对应

| Rubric | 主要技术决策 | 可验证证据 |
|---|---|---|
| A1 Food listing / expiry | TD-02、TD-03、TD-10 | `index.php`、库存状态与 expired treatment |
| A2 Request form / JavaScript | TD-02、TD-08 | `request.php`、`assets/js/request-validation.js` |
| A3 PHP processing / safe SQL / stock | TD-04、TD-05、TD-08 | transaction、`FOR UPDATE`、prepared statements、PRG |
| A4 Admin authentication | TD-07 | `login.php`、`includes/auth.php`、`logout.php` |
| A5 Dashboard / low stock / add item | TD-09、TD-10 | Operations workspace 与 protected actions |
| B1 Database and PDO | TD-03、TD-04 | `database/schema.sql`、`config/pdo.php`、ERD |
| B2 Validation and error handling | TD-08、TD-12 | browser/PHP/database 三层防线与 safe error path |
| C1 Architecture and choices | TD-01、TD-02、TD-11、TD-13 | system architecture 与本决策记录 |
| C2 Sitemap and flow | TD-02、TD-04、TD-09 | sitemap、request flow、admin lifecycle diagram |
| C3/C4 Evidence and professionalism | 全部 | Word report、references、screenshots、README、submission checklist |

## 6. 维护原则

新功能只有在回答以下问题后才应进入实现：

1. 它是 rubric、真实用户流程，还是单纯看起来丰富？
2. 它改变了什么持久状态，失败时怎样 rollback？
3. 重复提交会不会产生第二次副作用？
4. 普通访客、管理员与数据库各自能看到什么？
5. 它是否需要修改 schema、diagram、FR/NFR、测试和报告？

如果一个改动无法清楚回答这些问题，先不要写 code。对这个项目而言，克制不是少做功能，而是让每一个已经存在的功能都有明确边界。
