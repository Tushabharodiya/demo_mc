<main role="main" class="container">
    <div class="row">
        <div class="col col-sm-6 col-md-3">
            <div class="card text-white bg-secondary my-3">
              <div class="card-header text-center">Mods Data</div>
              <div class="card-body text-center">
                <img class="mb-4" src="<?php echo base_url();?>source/image/icon-banner.png" alt="android" height="50" width="50">
                <h6 class="small">Category : <?php if($categoryModsPublishCount != null){ echo $categoryModsPublishCount ?> <?php } else { ?> 0 <?php } ?> - <?php if($categoryModsUnpublishCount != null){ echo $categoryModsUnpublishCount ?> <?php } else { ?> 0 <?php } ?></h6>
                <h6 class="small">Data : <?php if($modsPublishCount != null){ echo $modsPublishCount ?> <?php } else { ?> 0 <?php } ?> - <?php if($modsUnpublishCount != null){ echo $modsUnpublishCount ?> <?php } else { ?> 0 <?php } ?></h6>
              </div>
            </div>
        </div>
        <div class="col col-sm-6 col-md-3">
            <div class="card text-white bg-secondary my-3">
              <div class="card-header text-center">Addons Data</div>
              <div class="card-body text-center">
                <img class="mb-4" src="<?php echo base_url();?>source/image/icon-banner.png" alt="android" height="50" width="50">
                <h6 class="small">Category : <?php if($categoryAddonsPublishCount != null){ echo $categoryAddonsPublishCount ?> <?php } else { ?> 0 <?php } ?> - <?php if($categoryAddonsUnpublishCount != null){ echo $categoryAddonsUnpublishCount ?> <?php } else { ?> 0 <?php } ?></h6>
                <h6 class="small">Data : <?php if($addonsPublishCount != null){ echo $addonsPublishCount ?> <?php } else { ?> 0 <?php } ?> - <?php if($addonsUnpublishCount != null){ echo $addonsUnpublishCount ?> <?php } else { ?> 0 <?php } ?></h6>
              </div>
            </div>
        </div>
        <div class="col col-sm-6 col-md-3">
            <div class="card text-white bg-secondary my-3">
              <div class="card-header text-center">Maps Data</div>
              <div class="card-body text-center">
                <img class="mb-4" src="<?php echo base_url();?>source/image/icon-banner.png" alt="android" height="50" width="50">
                <h6 class="small">Category : <?php if($categoryMapsPublishCount != null){ echo $categoryMapsPublishCount ?> <?php } else { ?> 0 <?php } ?> - <?php if($categoryMapsUnpublishCount != null){ echo $categoryMapsUnpublishCount ?> <?php } else { ?> 0 <?php } ?></h6>
                <h6 class="small">Data : <?php if($mapsPublishCount != null){ echo $mapsPublishCount ?> <?php } else { ?> 0 <?php } ?> - <?php if($mapsUnpublishCount != null){ echo $mapsUnpublishCount ?> <?php } else { ?> 0 <?php } ?></h6>
              </div>
            </div>
        </div>
        <div class="col col-sm-6 col-md-3">
            <div class="card text-white bg-secondary my-3">
              <div class="card-header text-center">Seeds Data</div>
              <div class="card-body text-center">
                <img class="mb-4" src="<?php echo base_url();?>source/image/icon-banner.png" alt="android" height="50" width="50">
                <h6 class="small">Category : <?php if($categorySeedsPublishCount != null){ echo $categorySeedsPublishCount ?> <?php } else { ?> 0 <?php } ?> - <?php if($categorySeedsUnpublishCount != null){ echo $categorySeedsUnpublishCount ?> <?php } else { ?> 0 <?php } ?></h6>
                <h6 class="small">Data : <?php if($seedsPublishCount != null){ echo $seedsPublishCount ?> <?php } else { ?> 0 <?php } ?> - <?php if($seedsUnpublishCount != null){ echo $seedsUnpublishCount ?> <?php } else { ?> 0 <?php } ?></h6>
              </div>
            </div>
        </div>
        <div class="col col-sm-6 col-md-3">
            <div class="card text-white bg-secondary my-3">
              <div class="card-header text-center">Textures Data</div>
              <div class="card-body text-center">
                <img class="mb-4" src="<?php echo base_url();?>source/image/icon-banner.png" alt="android" height="50" width="50">
                <h6 class="small">Category : <?php if($categoryTexturesPublishCount != null){ echo $categoryTexturesPublishCount ?> <?php } else { ?> 0 <?php } ?> - <?php if($categoryTexturesUnpublishCount != null){ echo $categoryTexturesUnpublishCount ?> <?php } else { ?> 0 <?php } ?></h6>
                <h6 class="small">Data : <?php if($texturesPublishCount != null){ echo $texturesPublishCount ?> <?php } else { ?> 0 <?php } ?> - <?php if($texturesUnpublishCount != null){ echo $texturesUnpublishCount ?> <?php } else { ?> 0 <?php } ?></h6>
              </div>
            </div>
        </div>
        <div class="col col-sm-6 col-md-3">
            <div class="card text-white bg-secondary my-3">
              <div class="card-header text-center">Shaders Data</div>
              <div class="card-body text-center">
                <img class="mb-4" src="<?php echo base_url();?>source/image/icon-banner.png" alt="android" height="50" width="50">
                <h6 class="small">Category : <?php if($categoryShadersPublishCount != null){ echo $categoryShadersPublishCount ?> <?php } else { ?> 0 <?php } ?> - <?php if($categoryShadersUnpublishCount != null){ echo $categoryShadersUnpublishCount ?> <?php } else { ?> 0 <?php } ?></h6>
                <h6 class="small">Data : <?php if($shadersPublishCount != null){ echo $shadersPublishCount ?> <?php } else { ?> 0 <?php } ?> - <?php if($shadersUnpublishCount != null){ echo $shadersUnpublishCount ?> <?php } else { ?> 0 <?php } ?></h6>
              </div>
            </div>
        </div>
        <div class="col col-sm-6 col-md-3">
            <div class="card text-white bg-secondary my-3">
              <div class="card-header text-center">Skins Data</div>
              <div class="card-body text-center">
                <img class="mb-4" src="<?php echo base_url();?>source/image/icon-banner.png" alt="android" height="50" width="50">
                <h6 class="small">Category : <?php if($categorySkinsPublishCount != null){ echo $categorySkinsPublishCount ?> <?php } else { ?> 0 <?php } ?> - <?php if($categorySkinsUnpublishCount != null){ echo $categorySkinsUnpublishCount ?> <?php } else { ?> 0 <?php } ?></h6>
                <h6 class="small">Data : <?php if($skinsPublishCount != null){ echo $skinsPublishCount ?> <?php } else { ?> 0 <?php } ?> - <?php if($skinsUnpublishCount != null){ echo $skinsUnpublishCount ?> <?php } else { ?> 0 <?php } ?></h6>
              </div>
            </div>
        </div>

        <div class="col col-sm-6 col-md-3">
            <div class="card text-white bg-secondary my-3">
              <div class="card-header text-center">Search</div>
              <div class="card-body text-center">
                <img class="mb-4" src="<?php echo base_url();?>source/image/icon-banner.png" alt="android" height="50" width="50">
                <h6><?php if($searchPublishCount != null){ echo $searchPublishCount ?> <?php } else { ?> 0 <?php } ?> - <?php if($searchAddedCount != null){ echo $searchAddedCount ?> <?php } else { ?> 0 <?php } ?></h6>
                <p class="card-text small">Search are Published</p>
              </div>
            </div>
        </div>
    </div>
</main>
