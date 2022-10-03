import {optimize} from "svgo/lib/svgo";
/*
Unoptimized SVGs: https://unoptimized--svg-icon-stress-test.netlify.app/
Source: https://cloudfour.com/thinks/svg-icon-stress-test/
 */
const svgString = "<svg width=\"24px\" height=\"24px\" viewBox=\"0 0 24 24\" version=\"1.1\" xmlns=\"http://www.w3.org/2000/svg\" xmlns:xlink=\"http://www.w3.org/1999/xlink\">\n" +
    "  <title>magnifying-glass</title>\n" +
    "  <g id=\"magnifying-glass-magnifying-glass\" stroke=\"none\" stroke-width=\"1\" fill=\"none\" fill-rule=\"evenodd\">\n" +
    "    <g id=\"magnifying-glass-Group-5\" transform=\"translate(3.000000, 3.000000)\" stroke=\"currentColor\" stroke-width=\"3\">\n" +
    "      <path d=\"M7.5,15 C11.642,15 15,11.642 15,7.5 C15,3.358 11.642,0 7.5,0 C3.358,0 0,3.358 0,7.5 C0,11.642 3.358,15 7.5,15 Z\" id=\"magnifying-glass-Stroke-1\"></path>\n" +
    "      <line x1=\"14\" y1=\"14\" x2=\"19\" y2=\"19\" id=\"magnifying-glass-Stroke-3\" stroke-linecap=\"round\"></line>\n" +
    "    </g>\n" +
    "  </g>\n" +
    "</svg>";
const result = optimize(svgString, {
    // optional but recommended field
    path: 'path-to.svg',
    // all config fields are also available here
    multipass: true,
});
const optimizedSvgString = result.data;
console.log(result);