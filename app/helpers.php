<?php
if (!function_exists('docker_secret')) {
    /**
     * Read value from named secret
     *
     * @param string $name
     * @return string Secret string or name if secret is not available/accessible
     */
    function docker_secret($name) {
        \Log::warning("You are using a docker secrets function with no effect!", [
            "file" => __FILE__,
            "function" => __FUNCTION__,
            "line" => __LINE__,
        ]);

        return $name;
    }
}
