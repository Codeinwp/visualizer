# delete all charts
wp post delete $(wp post list --post_type='visualizer' --format=ids) --force