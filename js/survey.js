/**
 * Initialize the formbricks survey.
 * 
 * @see https://github.com/formbricks/setup-examples/tree/main/html
 */
window.addEventListener('themeisle:survey:loaded', function () {
    window?.tsdk_formbricks?.init?.({
        environmentId: "cltef8cut1s7wyyfxy3rlxzs5",
        apiHost: "https://app.formbricks.com",
        ...(window?.visualizerSurveyData ?? {}),
    });
});