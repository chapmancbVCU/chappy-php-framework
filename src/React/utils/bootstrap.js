import { 
    apiDelete, 
    apiError,
    apiGet,
    apiPatch,
    apiPost,
    apiPut,
    useAsync
} from '@chappy/utils/api';

import asset from '@chappy/utils/asset'
import cleanCurrency from '@chappy/utils/phpCurrency.js';
import documentTitle from '@chappy/utils/documentTitle';
import Forms from "@chappy/components/Forms";
import route from "@chappy/utils/route";

window.apiDelete = apiDelete;
window.apiError = apiError;
window.apiGet = apiGet;
window.apiPatch = apiPatch;
window.apiPost = apiPost;
window.apiPut = apiPut;
window.asset = asset;
window.cleanCurrency = cleanCurrency;
window.Forms = Forms;
window.route = route;
window.documentTitle = documentTitle;
window.useAsync = useAsync;