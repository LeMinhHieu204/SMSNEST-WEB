<?php
class GuidesController extends Controller
{
    public function index()
    {
        Auth::requireLogin();
        $guideModel = new Guide();
        $guides = $guideModel->getAll();
        $this->view->render('guides/index', [
            'pageTitle' => 'Guides',
            'guides' => $guides,
        ]);
    }

    public function detail()
    {
        Auth::requireLogin();
        $id = (int) ($_GET['id'] ?? 0);
        $guide = $id > 0 ? (new Guide())->getById($id) : null;
        $sectionGuides = [];
        if (!$guide) {
            $this->view->render('guides/detail', [
                'pageTitle' => 'Guide',
                'guide' => null,
                'sectionGuides' => $sectionGuides,
            ]);
            return;
        }
        $sectionName = $guide['section'] ?? '';
        if ($sectionName !== '') {
            $sectionGuides = (new Guide())->getBySection($sectionName);
        }
        $this->view->render('guides/detail', [
            'pageTitle' => $guide['title'] ?? 'Guide',
            'guide' => $guide,
            'sectionGuides' => $sectionGuides,
        ]);
    }
}
