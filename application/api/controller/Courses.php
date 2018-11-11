<?php

namespace app\api\controller;

use app\admin\model\course\course;
use app\admin\model\course\Category;
use app\common\controller\Api;
use think\Db;

/**
 * 课程接口
 */
class Courses extends Api
{

    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];
    protected $pagesize= 10;
    protected $userid=47;

    /**
     * 课程分类
     *
     * @ApiTitle    (课程的分类)
     * @ApiMethod   (GET)
     * @ApiRoute    (/api/courses/category)
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiReturnParams   (name="code", type="integer", required=true, sample="0")
     * @ApiReturnParams   (name="msg", type="string", required=true, sample="返回成功")
     * @ApiReturnParams   (name="data", type="object", sample="{'user_id':'int','user_name':'string','profile':{'email':'string','age':'integer'}}", description="扩展数据返回")
     * @ApiReturn   ({
    'code':'1',
    'mesg':'返回成功'
     * })
     */
    public function category()
    {
        //分类数据
        $cate=db('course_category')->select();
        $this->success("返回成功",$cate);
    }

    /**
     * 推荐课程
     *
     * @ApiTitle    (推荐课程)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/courses/recommend)
     * @ApiParams   (name="page", type="integer", required=true, description="页码")
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiReturnParams   (name="code", type="integer", required=true, sample="0")
     * @ApiReturnParams   (name="msg", type="string", required=true, sample="返回成功")
     * @ApiReturnParams   (name="data", type="object", sample="{'user_id':'int','user_name':'string','profile':{'email':'string','age':'integer'}}", description="扩展数据返回")
     * @ApiReturn   ({
    'code':'1',
    'mesg':'返回成功'
     * })
     */
    public function recommend()
    {
        $page =   (int)$this->request->post("page");
        $page = $page?$page-1:0;
        //分类数据
        $data=db('course')->alias('a')
            ->join('course_category b','b.id=a.course_category_id')
            ->order('flag desc,readnum desc')
            ->field('a.*,b.name as type_name')
            ->page($page,$this->pagesize)
            ->select();
//            ->field('id,title,coverimage,content,videfile,views,comments,auth,createtime')

        $allpage=db('course')->alias('a')
                ->join('course_category b','b.id=a.course_category_id')
                ->count();
        $pages['pageCount']=ceil($allpage/$this->pagesize);




        foreach($data as $key=>$val){
            $data[$key]['createtime']=date('Y-m-d',$val['createtime']);
        }


        $this->success("返回成功",$data,$pages);
    }

    /**
     * 获取分类课程
     *
     * @ApiTitle    (获取某分类的课程)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/courses/course)
     * @ApiParams   (name="type_id", type="integer", required=true, description="分类id")
     * @ApiParams   (name="page", type="integer", required=true, description="页码")
     * @ApiParams   (name="title", type="integer", required=false, description="搜索的标题")
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiReturnParams   (name="code", type="integer", required=true, sample="0")
     * @ApiReturnParams   (name="msg", type="string", required=true, sample="返回成功")
     * @ApiReturnParams   (name="data", type="object", sample="{'user_id':'int','user_name':'string','profile':{'email':'string','age':'integer'}}", description="扩展数据返回")
     * @ApiReturn   ({
    'code':'1',
    'mesg':'返回成功'
     * })
     */
    public function course()
    {
        $page   =   (int)$this->request->post("page");
        $typeid =  (int)$this->request->post("type_id");
        $search = $this->request->request('title');


        if($search){
            $where=['name'=>['like','%'.$search.'%']];
        }else{
            $where=['course_category_id'=>$typeid];
        };
//            ->field('id,title,coverimage,content,videfile,views,comments,auth,createtime')

        //分类数据
        $data=db('course')->where($where)->order('createtime desc')->page($page,$this->pagesize)->select();

        $allpage=db('course')->where($where)->count();
        $pages['pageCount']=ceil($allpage/$this->pagesize);


        foreach($data as $key=>$val){
            $data[$key]['createtime']=date('Y-m-d',$val['createtime']);
        }

        $this->success("返回成功",$data,$pages);
    }



    /**
     * 课程点赞接口
     *
     * @ApiTitle    (对某课程进行点赞)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/courses/course_zan)
     * @ApiParams   (name="course_id", type="integer", required=true, description="课程id")
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiReturnParams   (name="code", type="integer", required=true, sample="0")
     * @ApiReturnParams   (name="msg", type="string", required=true, sample="返回成功")
     * @ApiReturnParams   (name="data", type="object", sample="{'user_id':'int','user_name':'string','profile':{'email':'string','age':'integer'}}", description="扩展数据返回")
     * @ApiReturn   ({
    'code':'1',
    'mesg':'返回成功'
     * })
     */
    public function course_zan()
    {
        $userid=$this->userid;
        $course_id  =  (int)$this->request->request("course_id");

        $res=db('course_zan')->where(['user_id'=>$userid,'course_id'=>$course_id])->find();
        if($res){
             db('course_zan')->where(['id'=>$res['id']])->delete();
             db('course')->where('id', $course_id)->setDec('zan');

            $this->success("取消成功");
        }

        $insert['course_id']=$course_id;
        $insert['user_id']=$userid;
        $insert['createtime']=time();
        $res=db('course_zan')->insert($insert);
        if($res){
            //同步新增到course_comment表 赞+1
            db('course')->where('id', $course_id)->setInc('zan');

            $this->success("点赞成功");
        }else{
            $this->error('点赞失败');
        }
    }


    /**
     * 获取课程详情及评论接口
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/courses/detail)
     * @ApiParams   (name="page", type="integer", required=true, description="评论页码")
     * @ApiParams   (name="course_id", type="integer", required=true, description="课程id")
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiReturnParams   (name="code", type="integer", required=true, sample="0")
     * @ApiReturnParams   (name="msg", type="string", required=true, sample="返回成功")
     * @ApiReturnParams   (name="data", type="object", sample="{'user_id':'int','user_name':'string','profile':{'email':'string','age':'integer'}}", description="扩展数据返回")
     * @ApiReturn   ({
    'code':'1',
    'mesg':'返回成功'
     * })
     */
    public function detail()
    {
        $userid=$this->userid;

        $page   =  (int)$this->request->post("page");
        $page = $page?$page-1:0;

        $course_id  =  (int)$this->request->post("course_id");

        $data=[];
        //初次加载
        if($page===0){
            $data['detail'] =db('course')->where(['id'=>$course_id])->find();
            $data['detail']['createtime']=date('Y-m-d',$data['detail']['createtime']);

            //同步新增到course浏览数+1
            db('course')->where('id',$course_id)->setInc('readnum');

            //TODO 获取用户信息 判断用户是否点赞
            $collection=db('course_collection')->where(['user_id'=>$userid,'course_id'=>$course_id])->find();
            $data['is_collection']=$collection?1:0;

            $zan=db('course_zan')->where(['user_id'=>$userid,'course_id'=>$course_id])->find();
            $data['is_zan']=$zan?1:0;

            //获取该课程的视频列表
            $node=db('course_nodes')->where(['course_id'=>$course_id])->field('sort,title,desc,isviewlist')->select();
            $data['detail']['node']=$node;


        }

        $where=['course_id'=>$course_id,'pid'=>0];
        //获取第一级评论内容
        $data['comment']= db('course_comment')->alias('a')->join('user','user.id=a.user_id')
                        ->field('user.nickname,avatar,a.*')
                        ->where($where)
                        ->order('a.createtime desc')
                        ->page($page,$this->pagesize)
                        ->select();

        //分页
        $allpage=db('course_comment')->alias('a')->join('user','user.id=a.user_id')->count();
        $pages['pageCount']=ceil($allpage/$this->pagesize);



        //查询该用户对评论点赞的数量
        if($data['comment']){
            foreach($data['comment'] as $key=>$val){
                $data['comment'][$key]['createtime']=date('Y-m-d',$val['createtime']);
//                if($val['user_id']==$this->userid){
//                    $data['comment'][$key]['is_zan']=1;
//                }else{
//                    $data['comment'][$key]['is_zan']=0;
//                }
            }
        }


        $this->success("返回成功",$data,$pages);
    }


    /**
     * 提交评论接口
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/courses/comment)
     * @ApiParams   (name="course_id", type="integer", required=true, description="课程id")
     * @ApiParams   (name="content", type="string", required=true, description="评论信息")
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiReturnParams   (name="code", type="integer", required=true, sample="0")
     * @ApiReturnParams   (name="msg", type="string", required=true, sample="返回成功")
     * @ApiReturnParams   (name="data", type="object", sample="{'user_id':'int','user_name':'string','profile':{'email':'string','age':'integer'}}", description="扩展数据返回")
     * @ApiReturn   ({
    'code':'1',
    'mesg':'返回成功'
     * })
     */
    public function comment()
    {
        $userid=$this->userid;
        $course_id  =  (int)$this->request->request("course_id");
        $content=$this->request->request("content");

        $is_course=db('course_zan')->where(['user_id'=>$userid,'course_id'=>$course_id])->find();
        if(!$is_course){
            $this->success("评论成功");
        }

        $insert['course_id']=$course_id;
        $insert['content']=$content;
        $insert['user_id']=$userid;
        $insert['createtime']=time();
        $res=db('course_comment')->insert($insert);
        if($res){
            //同步新增到course表 评论数加1
            db('course')->where('id', $course_id)->setInc('comments');

            $this->success("评论成功");
        }else{
            $this->error('评论失败');
        }
    }


    /**
     * 评论点赞接口
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/courses/comment_zan)
     * @ApiParams   (name="comment_id", type="integer", required=true, description="评论id")
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiReturnParams   (name="code", type="integer", required=true, sample="0")
     * @ApiReturnParams   (name="msg", type="string", required=true, sample="返回成功")
     * @ApiReturnParams   (name="data", type="object", sample="{'user_id':'int','user_name':'string','profile':{'email':'string','age':'integer'}}", description="扩展数据返回")
     * @ApiReturn   ({
    'code':'1',
    'mesg':'返回成功'
     * })
     */
    public function comment_zan()
    {
        $userid=$this->userid;
        $comment_id  =  (int)$this->request->request("comment_id");
        $res=db('course_comment_zan')->where(['user_id'=>$userid,'comment_id'=>$comment_id])->find();
        if($res){

            db('course_comment_zan')->where(['id'=>$res['id']])->delete();
            //同步新增到course_comment表 赞-1
            db('course_comment')->where('id', $comment_id)->setDec('zan');

            $this->success("取消成功");
        }

        //无评论，返回成功
        $is_comment=db('course_comment')->where(['id'=>$comment_id])->find();
        if(!$is_comment){
            $this->success("点赞成功");
        }

        $insert['comment_id']=$comment_id;
        $insert['user_id']=$userid;
        $insert['createtime']=time();
        $res=db('course_comment_zan')->insert($insert);
        if($res){
            //同步新增到course_comment表 赞+1
            db('course_comment')->where('id', $comment_id)->setInc('zan');

            $this->success("点赞成功");
        }else{
            $this->error('点赞失败');
        }
    }


    /**
     * 收藏接口
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/courses/collection)
     * @ApiParams   (name="course_id", type="integer", required=true, description="课程id")
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiReturnParams   (name="code", type="integer", required=true, sample="0")
     * @ApiReturnParams   (name="msg", type="string", required=true, sample="返回成功")
     * @ApiReturnParams   (name="data", type="object", sample="{'user_id':'int','user_name':'string','profile':{'email':'string','age':'integer'}}", description="扩展数据返回")
     * @ApiReturn   ({
    'code':'1',
    'mesg':'返回成功'
     * })
     */
    public function collection()
    {
        $userid=$this->userid;

        $course_id= (int)$this->request->request("course_id");

        $is_course=db('course')->where(['id'=>$course_id])->find();
        if(!$is_course){
            $this->success("收藏成功");
        }

        $is_have=db('course_collection')->where(['course_id'=>$course_id,'user_id'=>$userid])->find();
        if($is_have){

            db('course_collection')->where(['id'=>$is_have['id']])->delete();
            $this->success("取消成功",[]);
        }

        $insert['course_id']=$course_id;
        $insert['user_id']=$userid;
        $insert['createtime']=time();
        $res=db('course_collection')->insert($insert);
        if(!$res){
            $this->error('收藏失败',[]);
        }
        $this->success("收藏成功",[]);
    }

    /**
     * 获取某条评论详情
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/courses/comment_detail)
     * @ApiParams   (name="comment_id", type="integer", required=true, description="该评论的id")
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiReturnParams   (name="code", type="integer", required=true, sample="0")
     * @ApiReturnParams   (name="msg", type="string", required=true, sample="返回成功")
     * @ApiReturnParams   (name="data", type="object", sample="{'user_id':'int','user_name':'string','profile':{'email':'string','age':'integer'}}", description="扩展数据返回")
     * @ApiReturn   ({
    'code':'1',
    'mesg':'返回成功'
     * })
     */

    public function comment_detail()
    {
        $comment_id= (int)$this->request->request("comment_id");
        //评论内容
//        $detail['detail']=db('course_comment')->alias('a')->join('user','user.id=a.user_id')
//                ->field('user.nickname,avatar,a.*')
//                ->where(['id'=>$comment_id])
//                ->find();
//        if($detail['detail']){
//            $detail['detail']['createtime']=date('Y-m-d H:i:s',$detail['detail']['createtime']);
//        }else{
//            $this->success("获取成功",[]);
//        }


        $children=db('course_comment')->alias('a')->join('course_comment b','b.pid=a.id')
//                ->join('user','user.id=a.user_id')
                ->field('*')
                ->select();

        dump($children);die;



    }




    protected function commentTree($id)
    {
        static $subs = array(); //子孙数组
        $children_data=db('course_comment')->alias('a')
                    ->where(['pid'=>$id])
                    ->join('user u','u.id=a.user_id')
                    ->field('user.nickname,avatar,a.*')
                    ->select();

        if($children_data){
            //子类存在数据
            foreach($children_data as $key=>$val){

            }
        }
        return $subs;
    }


    /**
     * 申请课程接口
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/courses/audit)
     * @ApiParams   (name="course_id", type="integer", required=true, description="课程的id")
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiReturnParams   (name="code", type="integer", required=true, sample="0")
     * @ApiReturnParams   (name="msg", type="string", required=true, sample="返回成功")
     * @ApiReturnParams   (name="data", type="object", sample="{'user_id':'int','user_name':'string','profile':{'email':'string','age':'integer'}}", description="扩展数据返回")
     * @ApiReturn   ({
    'code':'1',
    'mesg':'返回成功'
     * })
     */

    public function audit()
    {
        $course_id= (int)$this->request->request("course_id");

        $userid=$this->userid;

        $is_have=db('course_audit')->where(['course_id'=>$course_id,'user_id'=>$userid])->find();
        if($is_have){
            $this->success("已提交申请",[]);
        }

        $insert['user_id']=$userid;
        $insert['course_id']=$course_id;
        $res=db('course_audit')->insert($insert);
        if($res){
            $this->success("提交成功",[]);
        }else{
            $this->error("提交失败",[]);
        }

    }


    /**
     * 获取课程的课时详情接口
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/courses/nodes)
     * @ApiParams   (name="nodes_id", type="integer", required=true, description="课时的id")
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiReturnParams   (name="code", type="integer", required=true, sample="0")
     * @ApiReturnParams   (name="msg", type="string", required=true, sample="返回成功")
     * @ApiReturnParams   (name="data", type="object", sample="{'user_id':'int','user_name':'string','profile':{'email':'string','age':'integer'}}", description="扩展数据返回")
     * @ApiReturn   ({
    'code':'1',
    'mesg':'返回成功'
     * })
     */

    public function nodes_detail()
    {
        $nodes_id= (int)$this->request->request("nodes_id");

        $userid=$this->userid;
        //查看该课时是否为可体验
        $is_views=db('course_nodes')->where(['id'=>$nodes_id])->find();
        if(!$is_views){
            $this->success("获取成功",[]);
        }

        if($is_views['isviewlist']!=='可体验'){

            //不是体验课程判断是否申请了并且通过了申请
            $is_check=db('course_audit')->where(['user_id'=>$userid,'checklist'=>'通过'])->find();
        }


    }






}