<?php
function TrendCard($trendData, $title) {
?>
    <div class="bg-white drop-shadow-md rounded px-4 py-6">
        <div class="flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" 
                 class="icon icon-tabler icon-tabler-trending-up text-indigo-600" 
                 width="24" 
                 height="24" 
                 viewBox="0 0 24 24" 
                 stroke-width="1" 
                 stroke="currentcolor" 
                 fill="none" 
                 stroke-linecap="round" 
                 stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <polyline points="3 17 9 11 13 15 21 7" />
                <polyline points="14 7 21 7 21 14" />
            </svg>
            <h2 class="text-base font-semibold"><?php echo htmlspecialchars($title); ?></h2>
        </div>
        
        <div class="flex flex-col gap-2 mt-3">
            <?php while ($row = $trendData->fetch_assoc()): ?>
                <a href="?category_id=<?php echo htmlspecialchars($row["category_id"]); ?>" 
                   class="flex flex-col py-1 pl-8 hover:bg-indigo-50 transition-colors duration-200">
                    <span class="text-indigo-600 text-base font-semibold">
                        #<?php echo htmlspecialchars($row["category_title"]); ?>
                    </span>
                    <span class="text-gray-700 text-sm">
                        <?php echo number_format($row["post_count"]); ?> posts
                    </span>
                </a>
            <?php endwhile; ?>
        </div>
    </div>
<?php 
}
?>