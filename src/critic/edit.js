import { InnerBlocks, useBlockProps } from '@wordpress/block-editor';
import {
	BaseControl,
	Placeholder,
	TextControl,
} from '@wordpress/components';

import { __ } from '@wordpress/i18n';

import { Critic } from './icons';

import './editor.scss';

const Edit = ( { attributes, setAttributes } ) => {
	const blockProps = useBlockProps();

	return (
		<div { ...blockProps }>
			<Placeholder
				icon={ Critic }
				label="Critique"
				instructions="Utilisez ce bloc pour insérer une critique de film."
			>
				<div>
					<TextControl
						label="Titre"
						type="text"
						value={ attributes.title }
						onChange={ ( value ) =>
							setAttributes( { title: value } )
						}
					/>
					<TextControl
						label="Année"
						type="number"
						min={ 1800 }
						max={ 2050 }
						step={ 1 }
						value={ attributes.year }
						onChange={ ( value ) =>
							setAttributes( { year: parseInt( value ) } )
						}
					/>
					<TextControl
						label="Réalisation"
						type="text"
						value={ attributes.director }
						onChange={ ( value ) =>
							setAttributes( { director: value } )
						}
					/>
					<TextControl
						label="Affiche"
						type="text"
						value={ attributes.poster }
						onChange={ ( value ) =>
							setAttributes( { poster: value } )
						}
					/>
					<BaseControl
						label="Contenu de la critique"
					>
						<InnerBlocks />
					</BaseControl>
				</div>
			</Placeholder>
		</div>
	);
};

export default Edit;
