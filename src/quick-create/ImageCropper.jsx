import { Button, Spinner } from '@wordpress/components';
import { useEffect, useRef, useState, useCallback } from '@wordpress/element';

const TARGET_WIDTH  = 960;
const TARGET_HEIGHT = 540;
const CROP_WIDTH    = 960;
const CROP_HEIGHT   = 360;

/**
 * Image cropper component.
 *
 * Loads the TMDb image, resizes to 960×540, then lets the user
 * drag a 960×360 crop region vertically (horizontal is full-width).
 * Similar to the WordPress header-image cropper.
 */
const ImageCropper = ( { imageUrl, onCropComplete, onCancel } ) => {
	const canvasRef = useRef( null );
	const containerRef = useRef( null );

	const [ loading, setLoading ] = useState( true );
	const [ imageElement, setImageElement ] = useState( null );

	// The crop region Y offset (in scaled image coordinates)
	const [ cropY, setCropY ] = useState( 90 );
	const [ dragging, setDragging ] = useState( false );
	const [ dragStartY, setDragStartY ] = useState( 0 );
	const [ dragStartCropY, setDragStartCropY ] = useState( 0 );

	// Display scale (the canvas may be smaller than 960px)
	const [ displayScale, setDisplayScale ] = useState( 1 );

	/**
	 * Load and resize the image.
	 */
	useEffect( () => {
		const img = new Image();
		img.crossOrigin = 'anonymous';
		img.onload = () => {
			setImageElement( img );
			setLoading( false );
		};
		img.onerror = () => {
			console.error( 'Failed to load image:', imageUrl );
			setLoading( false );
		};
		img.src = imageUrl;
	}, [ imageUrl ] );

	/**
	 * Calculate display scale based on container width.
	 */
	useEffect( () => {
		if ( ! containerRef.current ) return;

		const updateScale = () => {
			const containerWidth = containerRef.current?.offsetWidth ?? TARGET_WIDTH;
			const scale = Math.min( 1, containerWidth / TARGET_WIDTH );
			setDisplayScale( scale );
		};

		updateScale();
		window.addEventListener( 'resize', updateScale );
		return () => window.removeEventListener( 'resize', updateScale );
	}, [ containerRef.current ] );

	/**
	 * Draw the image with crop overlay.
	 */
	useEffect( () => {
		if ( ! imageElement || ! canvasRef.current ) return;

		const canvas = canvasRef.current;
		const displayWidth  = Math.round( TARGET_WIDTH * displayScale );
		const displayHeight = Math.round( TARGET_HEIGHT * displayScale );

		canvas.width  = displayWidth;
		canvas.height = displayHeight;

		const ctx = canvas.getContext( '2d' );

		// Draw the resized image
		ctx.drawImage( imageElement, 0, 0, displayWidth, displayHeight );

		// Draw the dark overlay outside the crop region
		ctx.fillStyle = 'rgba(0, 0, 0, 0.5)';

		const scaledCropY      = cropY * displayScale;
		const scaledCropHeight = CROP_HEIGHT * displayScale;

		// Top overlay
		ctx.fillRect( 0, 0, displayWidth, scaledCropY );
		// Bottom overlay
		ctx.fillRect( 0, scaledCropY + scaledCropHeight, displayWidth, displayHeight - scaledCropY - scaledCropHeight );

		// Draw crop border
		ctx.strokeStyle = 'rgba(255, 255, 255, 0.8)';
		ctx.lineWidth = 2;
		ctx.setLineDash( [ 5, 5 ] );
		ctx.strokeRect( 0, scaledCropY, displayWidth, scaledCropHeight );
		ctx.setLineDash( [] );

		// Draw rule-of-thirds lines
		ctx.strokeStyle = 'rgba(255, 255, 255, 0.3)';
		ctx.lineWidth = 1;
		for ( let i = 1; i <= 2; i++ ) {
			const y = scaledCropY + ( scaledCropHeight / 3 ) * i;
			ctx.beginPath();
			ctx.moveTo( 0, y );
			ctx.lineTo( displayWidth, y );
			ctx.stroke();
		}
	}, [ imageElement, cropY, displayScale ] );

	/**
	 * Mouse / touch handlers for dragging.
	 */
	const getEventY = ( e ) => {
		if ( e.touches && e.touches.length ) {
			return e.touches[ 0 ].clientY;
		}
		return e.clientY;
	};

	const handleDragStart = useCallback( ( e ) => {
		e.preventDefault();
		setDragging( true );
		setDragStartY( getEventY( e ) );
		setDragStartCropY( cropY );
	}, [ cropY ] );

	const handleDragMove = useCallback( ( e ) => {
		if ( ! dragging ) return;
		e.preventDefault();

		const deltaY = ( getEventY( e ) - dragStartY ) / displayScale;
		let newCropY = dragStartCropY + deltaY;

		// Clamp to valid range
		newCropY = Math.max( 0, Math.min( TARGET_HEIGHT - CROP_HEIGHT, newCropY ) );

		setCropY( Math.round( newCropY ) );
	}, [ dragging, dragStartY, dragStartCropY, displayScale ] );

	const handleDragEnd = useCallback( () => {
		setDragging( false );
	}, [] );

	/**
	 * Register global mouse/touch move/end handlers when dragging.
	 */
	useEffect( () => {
		if ( dragging ) {
			window.addEventListener( 'mousemove', handleDragMove );
			window.addEventListener( 'mouseup', handleDragEnd );
			window.addEventListener( 'touchmove', handleDragMove, { passive: false } );
			window.addEventListener( 'touchend', handleDragEnd );
		}
		return () => {
			window.removeEventListener( 'mousemove', handleDragMove );
			window.removeEventListener( 'mouseup', handleDragEnd );
			window.removeEventListener( 'touchmove', handleDragMove );
			window.removeEventListener( 'touchend', handleDragEnd );
		};
	}, [ dragging, handleDragMove, handleDragEnd ] );

	/**
	 * Perform the actual crop and produce a Blob.
	 */
	const doCrop = () => {
		if ( ! imageElement ) return;

		// Create an off-screen canvas at full resolution
		const offscreen = document.createElement( 'canvas' );
		offscreen.width  = CROP_WIDTH;
		offscreen.height = CROP_HEIGHT;

		const ctx = offscreen.getContext( '2d' );

		// Draw the source image, scaled to 960×540, then crop the region
		// We need to compute source coordinates from the original image
		const scaleX = imageElement.naturalWidth / TARGET_WIDTH;
		const scaleY = imageElement.naturalHeight / TARGET_HEIGHT;

		ctx.drawImage(
			imageElement,
			0,                    // source x
			cropY * scaleY,       // source y
			imageElement.naturalWidth,               // source width
			CROP_HEIGHT * scaleY, // source height
			0,                    // dest x
			0,                    // dest y
			CROP_WIDTH,           // dest width
			CROP_HEIGHT           // dest height
		);

		offscreen.toBlob( ( blob ) => {
			if ( blob ) {
				onCropComplete( blob );
			}
		}, 'image/jpeg', 0.92 );
	};

	if ( loading ) {
		return (
			<div className="image-cropper-loading">
				<Spinner />
				<p>Chargement de l'image…</p>
			</div>
		);
	}

	if ( ! imageElement ) {
		return (
			<div className="image-cropper-error">
				<p>Impossible de charger l'image.</p>
				<Button variant="secondary" onClick={ onCancel }>Annuler</Button>
			</div>
		);
	}

	return (
		<div className="image-cropper" ref={ containerRef }>
			<p className="image-cropper-instructions">
				Déplacez la zone de recadrage pour sélectionner la portion visible (960 × 360 px).
			</p>
			<div
				className="image-cropper-canvas-wrapper"
				style={ { cursor: dragging ? 'grabbing' : 'grab' } }
			>
				<canvas
					ref={ canvasRef }
					onMouseDown={ handleDragStart }
					onTouchStart={ handleDragStart }
					style={ {
						display: 'block',
						width: '100%',
						maxWidth: `${ TARGET_WIDTH }px`,
					} }
				/>
			</div>
			<div className="image-cropper-actions">
				<Button variant="secondary" onClick={ onCancel }>
					Annuler
				</Button>
				<Button variant="primary" onClick={ doCrop }>
					Recadrer
				</Button>
			</div>
		</div>
	);
};

export default ImageCropper;
