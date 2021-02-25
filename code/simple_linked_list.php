<?php
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


Class SimpleLinkedList
{
    private $root_node;
    public function __construct(Node $root_node)
    {
        $this->root_node = $root_node;
    }

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