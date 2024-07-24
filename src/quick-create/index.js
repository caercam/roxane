import { createRoot } from '@wordpress/element';

import Editor from './Editor';

const episodeEditor = document.createElement('div'),
        movieEditor = document.createElement('div');

episodeEditor.style.display = 'inline-block';
movieEditor.style.display = 'inline-block';

document.querySelector('.page-title-action').after(episodeEditor, movieEditor);

createRoot( episodeEditor ).render( <Editor type="episode" /> );
createRoot( movieEditor ).render( <Editor type="movie" /> );