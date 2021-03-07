<?php
/*
 * 练习题
 * 1） 求单链表中有效节点个数
 * 2） 查找单链表中倒数第k个节点【新浪面试题】
 * 3） 单链表的反转【腾讯面试题】
 * 4） 从头到尾打印单链表【百度，要求方式1: 反转遍历。方式2: Stack栈】
 * 5） 合并两个有序的单链表，合并之后的链表依然有序
 */

/*
 * 单链表的反转【腾讯面试题】
 */
$root_node = new Node(0, '', '');
$simple_linked_list = new SimpleLinkedList($root_node);
$node1 = new Node('1', '宋江', '及时雨');
$node2 = new Node('2', '吴用', '智多星');
$node3 = new Node('3', '李逵', '小旋风');
$simple_linked_list->add($node1);
$simple_linked_list->add($node2);
$simple_linked_list->add($node3);
echo "反转前~~\n";
$simple_linked_list->display();
$simple_linked_list->reverse($root_node);
echo "反转后~~\n";
$simple_linked_list->display();


/*
 * 求单链表中有效节点个数
 * 其实就是链表的遍历
 */
/*
$root_node = new Node(0, '', '');
$simple_linked_list = new SimpleLinkedList($root_node);
$node1 = new Node('1', '宋江', '及时雨');
$node2 = new Node('2', '吴用', '智多星');
$node3 = new Node('3', '李逵', '小旋风');
$simple_linked_list->add($node1);
$simple_linked_list->add($node2);
$simple_linked_list->add($node3);
$simple_linked_list->display();
*/

/*
 * 查找单链表中倒数第k个节点【新浪面试题】
 * 1、获取链表长度
 * 2、遍历链表找到第(length - k)个即可
 */
/*
$root_node = new Node(0, '', '');
$simple_linked_list = new SimpleLinkedList($root_node);
$node1 = new Node('1', '宋江', '及时雨');
$node2 = new Node('2', '吴用', '智多星');
$node3 = new Node('3', '李逵', '小旋风');
$simple_linked_list->add($node1);
$simple_linked_list->add($node2);
$simple_linked_list->add($node3);
var_dump($simple_linked_list->getNodeByBackIndex(0));
*/


/*
顺序添加
$root_node = new Node(0, '', '');
$simple_linked_list = new SimpleLinkedList($root_node);
$node1 = new Node('1', '宋江', '及时雨');
$node2 = new Node('2', '吴用', '智多星');
$node3 = new Node('3', '李逵', '小旋风');
$simple_linked_list->add($node1);
$simple_linked_list->add($node2);
$simple_linked_list->add($node3);
$simple_linked_list->display();
*/

/*
按编号大小顺序添加
$root_node = new Node(0, '', '');
$simple_linked_list = new SimpleLinkedList($root_node);
$node1 = new Node('1', '宋江', '及时雨');
$node2 = new Node('2', '吴用', '智多星');
$node3 = new Node('3', '李逵', '小旋风');
$node4 = new Node('9', '晁盖', '哎哎a');
$simple_linked_list->addOrderByNo($node1);
$simple_linked_list->addOrderByNo($node4);
$simple_linked_list->addOrderByNo($node3);
$simple_linked_list->addOrderByNo($node2);
$simple_linked_list->display();
*/

Class SimpleLinkedList
{
    private $root_node;
    public function __construct(Node $root_node)
    {
        $this->root_node = $root_node;
    }

    /*
     * 顺序添加
     */
    public function add(Node $node)
    {
        $temp = $this->root_node;
        while (true)
        {
            if ($temp->next == null) {
                $temp->next = $node;
                break;
            }

            $temp = $temp->next;
        }
    }

    /*
     * 按编号大小顺序添加
     */
    public function addOrderByNo(Node $node)
    {
        if ($this->root_node->next == null) {
            $this->root_node->next = $node;
            return;
        }
        $temp = $this->root_node;
        while (true)
        {
            if ($temp->no == $node->no) exit("已经存在\n");
            if(is_null($temp->next) || $temp->next->no > $node->no) {
                $node->next = $temp->next;
                $temp->next = $node;
                return;
            }
            $temp = $temp->next;
        }
    }

    /*
     * 遍历链表
     */
    public function display()
    {
        if ($this->root_node->next == null) {
            echo "链表还没有数据~\n";exit();
        }
        $temp = $this->root_node->next;
        while (true)
        {
            if (empty($temp)) break;
            echo "{$temp->no},{$temp->name},{$temp->nike_name}\n";

            $temp = $temp->next;
        }
    }

    /*
     * 获取长度
     */
    public function getLength()
    {
        $length = 0;
        if ($this->root_node->next == null) {
            return $length;
        }

        $cur = $this->root_node->next;
        while ($cur != null) {
            $length++;
            $cur = $cur->next;
        }

        return $length;
    }

    /*
     * 获取第k个节点
     */
    public function getNodeByBackIndex($index)
    {
        if ($this->root_node->next == null) {
            exit('链表为空');
        }

        $length = $this->getLength();

        if ($index < 0 && $index > $length) {
            exit('index error');
        }

        $cur = $this->root_node->next;
        for ($i = 0; $i < $length - $index; $i++) {
            $cur = $cur->next;
        }
        return $cur;
    }

    /*
     * 单链表反转
     * a.定义一个反转根节点
     * b.从头到尾遍历原来的节点，每遍历一个节点就摘下，并放在新的链表的最前端
     * c.原来的根节点next指向reverse rootNode->next
     */
    public function reverse(Node $root_node)
    {
        //链表为空或者只有一个节点
        if ($root_node->next == null || $root_node->next->next == null) {
            return;
        }

        //反转根节点
        $reverse_root_node = new Node('0', '', '');
        //指针,用于遍历
        $cur = $root_node->next;
        //存放摘下的节点
        $reverse_node = null;

        while ($cur != null)
        {
            $reverse_node = $cur;
            $cur = $cur->next;
            $reverse_node->next = $reverse_root_node->next;
            $reverse_root_node->next = $reverse_node;
        }
    }
}

Class Node
{
    public $name;
    public $no;
    public $nike_name;
    public $next;

    public function __construct($no, $name, $nike_name)
    {
        $this->no = $no;
        $this->name = $name;
        $this->nike_name = $nike_name;
    }
}