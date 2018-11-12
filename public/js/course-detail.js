let app = '';
window.onload = function () {
    app = new Vue({
        el: '#app',
        data: {
            isHidden: true,
            openTitle: '展开全部',
            //获取课程数据
            detailList:[],
            //课时列表
            courseList:[],
            //评论输入的内容
            commentsContent: '',
            //评论发表聚焦
            isFocus: false,
            //发送按钮禁用
            isDisabled: true,
            //是否点击上传按钮
            isUploadImage:true,
            //上传图片地址数组
            imgList:[],
           /* //是否收藏
            isCollect:false,*/
            //页面页码
            pageNumber:1,
            //页面总页数
            pageCount:null,
            //加载更多
            loadMore:false,
            //文章点赞
            isLikeArt:false,
            likeArtNums:0,
            //课程状态0:可以申请，1：审核中，2：开放
            courseStatus:0,
            //是否展示卡片视图
            isShowCard:false,
            //课程评论
            commentsList:[],
            //页面跳转时传递的id
            passID:null,

        },
        mounted() {
            /*  //获取 tab页内容和页面初始化数据
              this.init();
              //获取轮播图数据
              this.sowingMap();*/
            //推荐内容
            // this.getRecommendList();
        },
        methods: {
            touchStart (e) {
                this.startY = e.targetTouches[0].pageY
            },
            touchMove (e) {
                if (e.targetTouches[0].pageY < this.startY) { // 上拉
                    if(this.loadMore){
                        this.judgeScrollBarToTheEnd()
                    }
                }
            },
            // 判断滚动条是否到底
            judgeScrollBarToTheEnd () {
                let innerHeight = document.querySelector('.active').clientHeight
                // 变量scrollTop是滚动条滚动时，距离顶部的距离
                let scrollTop = document.documentElement.scrollTop || window.pageYOffset || document.body.scrollTop
                // 变量scrollHeight是滚动条的总高度
                let scrollHeight = document.documentElement.clientHeight || document.body.scrollHeight
                // 滚动条到底部的条件
                if (scrollTop + scrollHeight >= innerHeight-6000) {
                    this.infiniteLoadDone()
                }
            },
            infiniteLoadDone () {
                let self = this;
                //总页数
                if(self.pageCount >self.pageNumber){
                    self.pageNumber +=1;
                    $.post('/api/courses/course', {
                        title: self.searchKeys,
                        token:localStorage.getItem('token'),
                        page:self.pageNumber
                    }, function (data) {
                        data.data.forEach(function (item,index) {
                            self.searchList.push(item)
                        });
                    });
                }else {
                    return
                }


            },

            //课程详情
            courseDetails:function () {
                let self = this;
                console.log(11111111111111111,self.passID)
                $.post('/api/courses/detail', {
                    token:localStorage.getItem('token'),
                    course_id: 1,
                    page:self.pageNumber
                }, function (data) {
                    console.log('获取某个分类课程',data.data.comment);
                    self.detailList = data.data;
                    self.courseList = data.data.detail.node;
                    self.commentsList = data.data.comment;
                    self.pageCount = data.page.pageCount;
                    self.loadMore = true;
                });

            },
            //展开列表
            openAllCourse: function () {
                if (this.isHidden) {
                    this.openTitle = '收起列表';
                }
                else {
                    this.openTitle = '展开全部';
                }
                this.isHidden = !this.isHidden;
            },
            //显示上传图片图标等
            hideSmile: function () {
                this.isFocus = true;
            },
            //隐藏上传图标
            showSmile: function () {
                console.log('show');
                this.isFocus = false;
            },
            //上传图片
            uploadImg: function () {
                document.getElementById('upload-img').click();
            },
            goBack: function () {
                history.go(-1);
            },
            //评论点赞
            likeComment:function (flag,id) {
                let self = this;
                $.post(' /api/courses/comment_zan', {
                    token:localStorage.getItem('token'),
                    comment_id: id
                }, function (data) {
                    console.log('评论点赞',data.data);
                    self.$nextTick(function () {
                        self.courseDetails();
                    })
                });
            },
            //文章点赞
            likeArticle:function(id,flag) {
                this.isLikeArt = flag;
                if(flag) {
                    this.likeArtNums = ++this.likeArtNums;
                }
                else {
                    this.likeArtNums = --this.likeArtNums;
                }
                let self = this;
                $.post('/api/courses/course_zan', {
                    token:localStorage.getItem('token'),
                    comment_id: id
                }, function (data) {
                    console.log('文章点赞',data.data);
                    self.$nextTick(function () {
                        self.courseDetails();
                    })
                });
            },
            //回复
            replay:function () {
                $('.emoji-wysiwyg-editor').focus();
            },
            //进入课时
            goToCourseHour:function (id) {
                console.log('进入课时',id)
                mui.openWindow({
                    //视频版
                    url:'/index/index/course_detail?id='+id
                    // 图文版
                    // url:'course-hour-detail-pictures.html?id=' + id
                })
            },
            shareCourse:function() {
                mui('#share-sheet').popover('toggle');
            },
            //收藏，取消收藏
            collect:function (flag) {
                let self = this;
                $.post('/api/courses/collection', {
                    token:localStorage.getItem('token'),
                    course_id: self.passID
                }, function (data) {
                    self.$nextTick(function () {
                        self.courseDetails();

                    })
                });
                if(flag) {
                    self.detailList.detail.is_collection = true;
                    mui.toast('已收藏');
                }
                else {
                    self.detailList.detail.is_collection = false;
                    mui.toast('已取消收藏');
                }
            },
            //点击发送按钮
            reviewBtn:function () {
                let self = this;
                $.post('/api/courses/comment', {
                    token:localStorage.getItem('token'),
                    course_id: self.passID,
                    content:self.commentsContent
                }, function (data) {
                    self.$nextTick(function () {
                        self.courseDetails();
                        self.commentsContent = '';
                    })
                });
            },
            goCommentsDetail:function (id) {
                //查看评论详情
                mui.openWindow({
                    url:'/index/index/comments?id='+id
                })

            },
            sendToFriend:function () {
                //发给好友
            },
            generateCard:function () {
                mui('#share-sheet').popover('toggle');
                //生成卡片
                this.isShowCard = true;
            },
            hidePreview:function () {
                //关闭图片预览
                this.isShowCard = false;
            },
            saveImg:function () {
                
            }
        },
        watch: {
            commentsContent: function (newVal, oldVal) {
                let self = this;
                if (newVal.trim() != '') {
                    self.isDisabled = false;
                } else {
                    console.log(2222)
                    self.isDisabled = true;
                }

            },
            isUploadImage:function (newVal,oldVal) {

            }
        },
        created: function () {
            let code = window.location.href.split('?')[1];
            this.passID = code.split('=')[1];
            this.$nextTick(function () {
                //获取课程详情
                this.courseDetails();
            })
        },
        beforeDestroy(){
            $(window).unbind('scroll');
        },
    });
    /**
     * 监听文件上传框变化
     */
    $("#upload-img").change(function () {
        app.hideSmile();
        let reads = new FileReader();
        let f = document.getElementById('upload-img').files[0];
        reads.readAsDataURL(f);
        reads.onload = function (e) {
            app.imgList.push(this.result);
            mui.toast('文件读取成功!');
        };
    });
    window.emojiPicker = new EmojiPicker({
        emojiable_selector: '[data-emojiable=true]',
        assetsPath: 'assets/emoji/img/',
        popupButtonClasses: 'fa fa-smile-o'
    });
    // Finds all elements with `emojiable_selector` and converts them to rich emoji input fields
    // You may want to delay this step if you have dynamically created input fields that appear later in the loading process
    // It can be called as many times as necessary; previously converted input fields will not be converted again
    window.emojiPicker.discover();
    document.querySelector('.emoji-wysiwyg-editor').addEventListener('focus', function () {
        app.hideSmile();
    });
    document.querySelector('.emoji-wysiwyg-editor').addEventListener('blur', function () {
        let elem = $(this);
        setTimeout(function () {
            if(app.commentsContent.length == 0 && app.imgList.length == 0) {
                app.showSmile();
            }
        },500);
        document.querySelector('#uploadImg').addEventListener('click', function () {
            app.isUploadImage = true;
            app.uploadImg();
        });
    });
    document.querySelector('.emoji-wysiwyg-editor').addEventListener('input', function () {
        app.commentsContent = $(this).text();
    });
};