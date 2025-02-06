<form method="POST" enctype="multipart/form-data">
    <div class="my-4 flex flex-col items-start gap-2 w-full">
        <?php if (!empty($share_success)) { ?>
            <span class="mb-2 text-sm text-emerald-600"><?php echo htmlspecialchars($share_success); ?></span>
        <?php } ?>
        <?php if (!empty($post_title_err)) { ?>
            <span class="mb-2 text-sm text-red-600"><?php echo htmlspecialchars($post_title_err); ?></span>
        <?php } ?>
        <?php if (!empty($post_image_err)) { ?>
            <span class="mb-2 text-sm text-red-600"><?php echo htmlspecialchars($post_image_err); ?></span>
        <?php } ?>
        <?php if (!empty($post_category_id_err)) { ?>
            <span class="mb-2 text-sm text-red-600"><?php echo htmlspecialchars($post_category_id_err); ?></span>
        <?php } ?>
        <?php if (!empty($share_post_error)) { ?>
            <span class="mb-2 text-sm text-red-600"><?php echo htmlspecialchars($share_post_error); ?></span>
        <?php } ?>
        <textarea 
            id="post-title" 
            name="post_title" 
            class="w-full bg-transparent border border-gray-300 pl-3 py-3 shadow-sm rounded text-sm focus:outline-none focus:border-indigo-700 resize-none placeholder-gray-500 text-gray-600" 
            placeholder="Share your thoughts with the world..." 
            rows="3"
        ></textarea>
        <div class="w-full flex justify-between gap-2">
            <div class="relative flex items-center">
                <select name="post_category_id" class="cursor-pointer block text-sm mr-4 py-2 px-4 rounded-full border-none text-sm font-semibold bg-indigo-50 text-indigo-700 hover:bg-indigo-100 focus:outline-none focus:border-indigo-700">
                    <?php
                    $categoryQuery = "SELECT * FROM category ORDER BY category_title";
                    $stmt = $link->prepare($categoryQuery);
                    $stmt->execute();
                    $categoryList = $stmt->get_result();

                    if ($categoryList->num_rows > 0) {
                        while ($row = $categoryList->fetch_assoc()) { ?>
                            <option <?php echo $row["category_id"] == $category_id ? "selected" : ""; ?> value="<?php echo $row["category_id"]; ?>">
                                #<?php echo htmlspecialchars(ucfirst($row["category_title"])); ?>
                            </option>
                    <?php }
                    } ?>
                </select>
                <input 
                    name="post_image" 
                    type="file" 
                    class="block w-full text-sm text-slate-500 file:cursor-pointer file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" 
                    accept="image/jpeg,image/png,image/jpg"
                />
            </div>
            <button 
                name="share_post" 
                class="justify-self-end text-white my-2 bg-indigo-700 transition duration-150 ease-in-out hover:bg-indigo-600 rounded-full px-6 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-600"
            >
                Share Post
            </button>
        </div>
    </div>
</form>