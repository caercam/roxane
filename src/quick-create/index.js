import { createRoot } from '@wordpress/element';

import Editor from './Editor';
import './editor.scss';

const episodeEditor = document.createElement('div'),
        movieEditor = document.createElement('div');

episodeEditor.style.display = 'inline-block';
movieEditor.style.display = 'inline-block';

const target = document.querySelector('.page-title-action')
console.log(target);
if ( target ) {
    target.after(episodeEditor, movieEditor);
    createRoot( episodeEditor ).render( <Editor type="episode" /> );
    createRoot( movieEditor ).render( <Editor type="movie" /> );
}