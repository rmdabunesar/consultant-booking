import { TextControl, SelectControl } from "@wordpress/components";

const SlotSettings = ({ settings, update }) => {
	return (
		<div style={{ maxWidth: "300px" }}>
		<TextControl
			label="Default Slot Duration (minutes)"
			type="number"
			value={settings.default_slot_duration || ""}
			onChange={(value) =>
			update("default_slot_duration", parseInt(value, 10))
			}
		/>

		<SelectControl
			label="Slot Interval Type"
			value={settings.slot_interval_type || "fixed"}
			options={[
			{ label: "Fixed", value: "fixed" },
			{ label: "Flexible", value: "flexible" },
			]}
			onChange={(value) => update("slot_interval_type", value)}
		/>
		</div>
	);
};

export default SlotSettings;
