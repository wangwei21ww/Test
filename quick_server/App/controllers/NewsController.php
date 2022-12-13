<?php
class NewsController extends BaseController
{
    /**
     * 添加咨询
     *
     * @param [type] $title
     * @param [type] $content
     * @param [type] $lang
     * @param [type] $source
     * @param [type] $images
     * @param [type] $tags
     * @return void
     */
    public function actionAdd($title, $content, $lang, $source, $images, $tags, $describe)
    {
        // $this->assertParam('title',$title);
        // $this->assertParam('content', $content);
        // $this->assertParam('lang', $lang);
        // $this->assertParam('source', $source);
        // $this->assertParam('images', $images);
        // $this->assertParam('tags', $tags);
        return (new News)->create([
            'title' => $title,
            'content' => $content,
            'lang' => $lang,
            'source' => $source,
            'images' => $images,
            'tags' => $tags,
            'describe' => $describe,
            'type' => 'in',
            'create_time' => time(),
            'update_time' => time()
        ]);
    }

    /**
     * 咨询列表
     *
     * @param integer $page
     * @param integer $pageSize
     * @return void
     */
    public function actionList($page = 1, $pageSize = 50)
    {
        $condition = 'type = :type';
        $bind = ['type'=>'in'];
        $filed = '*';
        return (new News)->getRecords('news',$filed,$condition,$bind,[],['id'],$page,$pageSize);
    }

    /**
     * 删除咨询
     *
     * @param [type] $id
     * @return void
     */
    public function actionDel($id)
    {
        return (new News)->getDb()->delete('news')->where('id='.$id)->query() == 1;
    }

    /**
     * 编辑咨询
     *
     * @param [type] $id
     * @param [type] $image
     * @param [type] $lang
     * @param [type] $source
     * @param [type] $content
     * @param [type] $tags
     * @param [type] $title
     * @return void
     */
    public function actionEdit($id, $lang, $source, $content, $tags, $title, $describe)
    {
        $this->assertParam('id',$id);
        return (new News)->getDb()->update('news')->cols([
            'lang'=> $lang,
            'source'=> $source,
            'content'=> $content,
            'tags'=> $tags,
            'title'=> $title,
            'describe'=> $describe,
        ])->where("id = ".$id)->query() == 1;
    }
}