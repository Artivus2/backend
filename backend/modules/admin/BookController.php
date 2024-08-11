<?php

namespace app\controllers;

use app\models\Author;
use app\models\Book;
use app\models\BookAuthor;
use app\models\BookSearch;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

/**
 * BookController implements the CRUD actions for Book model.
 */
class BookController extends Controller
{

    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all Book models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new BookSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Book model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'book' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Book model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $book = $this->getValidatedBookFromPost();
        $authors = $this->getValidatedAuthorsFromPost();

        if (!$this->request->isPost) {
            return $this->render('create', [
                'book' => $book,
                'authors' => $authors,
            ]);
        }

        if ($book && $authors) {
            if ($book->save(false)) {
                foreach ($authors as $author) {
                    $isNewRecord = $author->isNewRecord;
                    $author->save(false);
                    if ($isNewRecord) {
                        $book->link('authors', $author);
                    }
                }
            }
        }

        return $this->redirect(['view', 'id' => $book->id]);
    }

    /**
     * @param int|null $id
     * @return Book
     * @throws NotFoundHttpException
     */
    private function getValidatedBookFromPost(int $id = null): Book
    {
        $book = $id ? $this->findModel($id) : new Book();

        if ($this->request->isPost) {
            if ($book->load($this->request->post()) && $book->validate($this->request->post())) {
                $book->coverImage = UploadedFile::getInstances($book, 'cover_image');
                $imageName = $book->upload();
                if ($book->coverImage && $imageName) {
                    //TODO: сделать удаление старого файла
                    $book->setAttribute('cover_image', $imageName);
                }
                elseif (!$book->isNewRecord) {
                    $book->setAttribute('cover_image', $book->getOldAttribute('cover_image'));
                }
                return $book;
            }
        } else {
            $book->loadDefaultValues();
        }

        return $book;
    }

    /**
     * @param Book|null $book
     * @return array|Author[]
     */
    private function getValidatedAuthorsFromPost($id)
    {
        
        $authors = \Yii::$app->request->post('authors',[]);

        // if (Author::loadMultiple($authors, \Yii::$app->request->post())
        //         && Author::validateMultiple($authors)
        //     ) {
        //         return $authors;
        //     }
        // if (Author::loadMultiple($authors, \Yii::$app->request->post())
        //         && Author::validateMultiple($authors)
        //     ) {
        //         return $authors;
        //     }
        // if ($book) {
        //     $authors = $book->authors;
        // }

        // if (!count($authors)) {
        //     $authors = [new Author()];
        // }

        // if ($this->request->isPost) {
        //     $countFromPost = count(\Yii::$app->request->post('authors', []));
        //     $countFromTable = count($authors);
        //     for ($i= $countFromTable; $i<$countFromPost; $i++) {
        //         $authors[] = new Author();
        //     }
        //     if (Author::loadMultiple($authors, \Yii::$app->request->post())
        //         && Author::validateMultiple($authors)
        //     ) {
        //         return $authors;
        //     }
        // } else {
        //     $authors[0]->loadDefaultValues();
        // }

        return $authors;
    }

    /**
     * @param int|null $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionAddAuthor(int $id = null)
    {
        $book = $this->getValidatedBookFromPost($id);
        $authors = $this->getValidatedAuthorsFromPost($book);

        if ($book && $authors) {
            $authors[] = new Author();

            $view = $book->isNewRecord ? 'create' : 'update';

            return $this->render($view, [
                'book' => $book,
                'authors' => $authors,
            ]);
        }

        return $this->render('view', [
            'book' => $book,
            'authors' => $authors,
        ]);
    }

    /**
     * Updates an existing Book model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate(int $id)
    {
        $book = $this->getValidatedBookFromPost($id);
        $authors = BookAuthor::find()->where(['book_id' => $id])->all();
        //$authors=\Yii::$app->request->post('authors');
        if (!$this->request->isPost) {
            return $this->render('update', [
                'book' => $book,
                'authors' => $authors,
            ]);
        }

        // if ($book && $authors) {
        //     $authors[] = new Author();

            
        // }

        // if ($book->save(false)) {

        // foreach ($authors as $author) {
        //     $authorbook = new BookAuthor(['book_id' => $book->id, 'author_id' => $author]);
        //     $authorbook->save();
        //     // $isNewRecord = $author->isNewRecord;
        //     // //$author->save(false);
        //     // if ($isNewRecord) {
        //     //     $book->link('authors', $author);
        //     // }

        // }
        // }
            return var_dump($book);
        //return $this->redirect(['view', 'id' => $book->id]);
    }

    /**
     * Deletes an existing Book model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $book = $this->findModel($id);
        foreach ($book->authors as $author) {
            $author->delete();
        }
        $book->delete();

        // TODO: удаление файла

        return $this->redirect(['index']);
    }

    /**
     * Finds the Book model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Book the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Book::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
